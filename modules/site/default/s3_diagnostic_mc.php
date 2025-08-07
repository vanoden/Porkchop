<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('configure site');
	
	// Initialize diagnostic results
	$diagnostic_results = array();
	$diagnostic_complete = false;
	
	// Run diagnostic if requested
	if (isset($_REQUEST['run_diagnostic']) && $_REQUEST['run_diagnostic'] == 'true') {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
		} else {
			$diagnostic_complete = true;
			ob_start();
			
			try {
				echo "=== COMPREHENSIVE S3 DIAGNOSTIC ===\n";
				echo "Generated: " . date('Y-m-d H:i:s') . "\n";
				echo "Host: " . gethostname() . "\n";
				echo "PHP Version: " . PHP_VERSION . "\n\n";

				echo "=== ENVIRONMENT CHECK ===\n";

				// Check required directories and files
				$required_paths = [
					'INCLUDES' => INCLUDES,
					'THIRD_PARTY' => THIRD_PARTY,
					'CLASS_PATH' => CLASS_PATH
				];

				foreach ($required_paths as $name => $path) {
					if (file_exists($path)) {
						echo "✓ $name path exists: $path\n";
					} else {
						echo "✗ $name path missing: $path\n";
					}
				}

				// Check for AWS SDK
				if (class_exists('Aws\S3\S3Client')) {
					echo "✓ AWS SDK S3Client available\n";
				} else {
					echo "✗ AWS SDK S3Client missing\n";
				}

				echo "\n=== AWS ENVIRONMENT CHECK ===\n";

				// Check AWS environment variables
				$aws_env_vars = [
					'AWS_ACCESS_KEY_ID',
					'AWS_SECRET_ACCESS_KEY', 
					'AWS_DEFAULT_REGION',
					'AWS_REGION',
					'AWS_SESSION_TOKEN',
					'AWS_PROFILE'
				];

				foreach ($aws_env_vars as $var) {
					$value = getenv($var);
					if ($value !== false) {
						if (strpos($var, 'SECRET') !== false) {
							echo "✓ $var = [REDACTED]\n";
						} else {
							echo "✓ $var = $value\n";
						}
					} else {
						echo "  $var = (not set)\n";
					}
				}

				// Check AWS credential files
				$home = getenv('HOME') ?: '/root';
				$aws_files = [
					"$home/.aws/credentials",
					"$home/.aws/config"
				];

				foreach ($aws_files as $file) {
					if (file_exists($file)) {
						echo "✓ AWS file exists: $file\n";
						if (is_readable($file)) {
							echo "  - File is readable\n";
						} else {
							echo "  - File is NOT readable\n";
						}
					} else {
						echo "  AWS file missing: $file\n";
					}
				}

				// Check EC2 metadata service (for IAM roles)
				echo "\n=== EC2 METADATA SERVICE CHECK ===\n";
				$metadata_urls = [
					'http://169.254.169.254/latest/meta-data/iam/security-credentials/',
					'http://169.254.169.254/latest/dynamic/instance-identity/document'
				];

				foreach ($metadata_urls as $url) {
					$context = stream_context_create([
						'http' => [
							'timeout' => 5,
							'method' => 'GET'
						]
					]);
					
					$result = @file_get_contents($url, false, $context);
					if ($result !== false) {
						echo "✓ Metadata service accessible: $url\n";
						if (strpos($url, 'security-credentials') !== false) {
							echo "  Available roles: " . str_replace("\n", ", ", trim($result)) . "\n";
						}
					} else {
						echo "  Metadata service not accessible: $url\n";
					}
				}

				echo "\n=== STORAGE REPOSITORY CHECK ===\n";

				// List all repositories
				$repositoryList = new \Storage\RepositoryList();
				$repositories = $repositoryList->find(['type' => 's3']);
				
				if (empty($repositories)) {
					echo "✗ No S3 repositories found\n";
					echo "Checking all repositories:\n";
					$all_repos = $repositoryList->find();
					foreach ($all_repos as $repo) {
						echo "  - {$repo->name} (Type: {$repo->type}, Status: {$repo->status})\n";
					}
				} else {
					echo "✓ Found " . count($repositories) . " S3 repository(ies)\n";
					
					foreach ($repositories as $repo) {
						echo "\n--- Repository: {$repo->name} ---\n";
						echo "Code: {$repo->code}\n";
						echo "Status: {$repo->status}\n";
						
						// Get metadata
						$bucket = $repo->getMetadata('bucket');
						$region = $repo->getMetadata('region') ?: 'us-east-1';
						$accessKey = $repo->getMetadata('accessKey');
						$secretKey = $repo->getMetadata('secretKey');
						
						echo "Bucket: " . ($bucket ?: 'NOT SET') . "\n";
						echo "Region: $region\n";
						echo "Access Key: " . ($accessKey ? 'SET (' . substr($accessKey, 0, 8) . '...)' : 'NOT SET (using IAM role)') . "\n";
						echo "Secret Key: " . ($secretKey ? 'SET (length: ' . strlen($secretKey) . ')' : 'NOT SET (using IAM role)') . "\n";
						
						// Test the specific bucket that's failing
						if ($bucket === 'spectros-test-site-images') {
							echo "\n🎯 THIS IS THE FAILING BUCKET - DETAILED ANALYSIS:\n";
							
							// Test basic S3 client creation
							try {
								if ($accessKey && $secretKey) {
									echo "Testing with explicit credentials...\n";
									$s3Client = new \Aws\S3\S3Client([
										'region' => $region,
										'version' => 'latest',
										'credentials' => [
											'key' => $accessKey,
											'secret' => $secretKey
										]
									]);
								} else {
									echo "Testing with IAM role/instance profile...\n";
									$s3Client = new \Aws\S3\S3Client([
										'region' => $region,
										'version' => 'latest'
									]);
								}
								
								echo "✓ S3 Client created successfully\n";
								
								// Test bucket existence
								echo "Testing bucket existence...\n";
								$exists = $s3Client->doesBucketExist($bucket);
								if ($exists) {
									echo "✓ Bucket exists and is accessible\n";
									
									// Test bucket location
									try {
										$location = $s3Client->getBucketLocation(['Bucket' => $bucket]);
										$actual_region = $location['LocationConstraint'] ?: 'us-east-1';
										echo "✓ Bucket location: $actual_region\n";
										
										if ($actual_region !== $region) {
											echo "⚠️  WARNING: Bucket is in $actual_region but client configured for $region\n";
										}
									} catch (Exception $e) {
										echo "✗ Failed to get bucket location: " . $e->getMessage() . "\n";
									}
									
									// Test list objects (read permission)
									try {
										$result = $s3Client->listObjects(['Bucket' => $bucket, 'MaxKeys' => 1]);
										echo "✓ List objects successful (read permission OK)\n";
									} catch (Exception $e) {
										echo "✗ List objects failed: " . $e->getMessage() . "\n";
									}
									
									// Test write permission with small object
									try {
										$test_key = 'diagnostic-test-' . time() . '.txt';
										$s3Client->putObject([
											'Bucket' => $bucket,
											'Key' => $test_key,
											'Body' => 'Diagnostic test file',
											'Metadata' => ['Source' => 'Web Diagnostic']
										]);
										echo "✓ Write test successful\n";
										
										// Clean up test object
										$s3Client->deleteObject(['Bucket' => $bucket, 'Key' => $test_key]);
										echo "✓ Test cleanup successful\n";
										
									} catch (\Aws\S3\Exception\S3Exception $e) {
										echo "✗ Write test failed: " . $e->getAwsErrorCode() . " - " . $e->getMessage() . "\n";
									} catch (Exception $e) {
										echo "✗ Write test failed: " . $e->getMessage() . "\n";
									}
									
								} else {
									echo "✗ Bucket does not exist or is not accessible\n";
									
									// Try to list all buckets to see what's available
									try {
										$buckets = $s3Client->listBuckets();
										echo "Available buckets:\n";
										foreach ($buckets['Buckets'] as $bucket_info) {
											echo "  - " . $bucket_info['Name'] . "\n";
										}
									} catch (Exception $e) {
										echo "✗ Failed to list buckets: " . $e->getMessage() . "\n";
									}
								}
								
							} catch (\Aws\S3\Exception\S3Exception $e) {
								echo "✗ AWS S3 Exception: " . $e->getAwsErrorCode() . " - " . $e->getMessage() . "\n";
							} catch (Exception $e) {
								echo "✗ General Exception: " . $e->getMessage() . "\n";
							}
						}
						
						// Test using repository instance
						echo "\nTesting through repository instance...\n";
						try {
							$instance = $repo->getInstance();
							if ($instance->connect()) {
								echo "✓ Repository connection successful\n";
								
								if (method_exists($instance, 'testWritePermissions')) {
									if ($instance->testWritePermissions()) {
										echo "✓ Write permissions test passed\n";
									} else {
										echo "✗ Write permissions test failed: " . $instance->error() . "\n";
									}
								}
							} else {
								echo "✗ Repository connection failed: " . $instance->error() . "\n";
							}
						} catch (Exception $e) {
							echo "✗ Repository test exception: " . $e->getMessage() . "\n";
						}
					}
				}

				echo "\n=== SITE CONFIGURATION CHECK ===\n";

				// Check site configuration for website_images
				$config = new \Site\Configuration();
				$config->get('website_images');
				if ($config->value) {
					echo "✓ Website images repository configured: {$config->value}\n";
					
					// Load that repository
					$repo = new \Storage\Repository();
					$repo->get($config->value);
					if ($repo->id) {
						echo "✓ Repository found: {$repo->name} (Type: {$repo->type})\n";
					} else {
						echo "✗ Repository with code '{$config->value}' not found!\n";
					}
				} else {
					echo "✗ No website_images configuration found\n";
				}

				echo "\n=== NETWORK CONNECTIVITY TEST ===\n";

				// Test connectivity to AWS endpoints
				$aws_endpoints = [
					"s3.us-east-1.amazonaws.com",
					"s3.amazonaws.com",
					"sts.amazonaws.com"
				];

				foreach ($aws_endpoints as $endpoint) {
					$start = microtime(true);
					$fp = @fsockopen($endpoint, 443, $errno, $errstr, 10);
					$time = round((microtime(true) - $start) * 1000, 2);
					
					if ($fp) {
						echo "✓ Connectivity to $endpoint:443 OK ({$time}ms)\n";
						fclose($fp);
					} else {
						echo "✗ Cannot connect to $endpoint:443 - $errstr ($errno)\n";
					}
				}

				echo "\n=== SYSTEM INFORMATION ===\n";
				echo "Operating System: " . php_uname() . "\n";
				echo "User: " . get_current_user() . " (UID: " . getmyuid() . ")\n";
				echo "Working Directory: " . getcwd() . "\n";
				echo "Memory Limit: " . ini_get('memory_limit') . "\n";
				echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
				echo "Temp Directory: " . sys_get_temp_dir() . "\n";

				// Check if running in Docker
				if (file_exists('/.dockerenv')) {
					echo "✓ Running in Docker container\n";
				} else {
					echo "  Not running in Docker\n";
				}

				echo "\n=== DIAGNOSTIC COMPLETE ===\n";
				echo "If S3 uploads are still failing after reviewing this output,\n";
				echo "the most likely issues are:\n";
				echo "1. Bucket doesn't exist or wrong region\n";
				echo "2. IAM permissions insufficient\n";
				echo "3. Network connectivity issues\n";
				echo "4. Incorrect AWS credentials\n";

			} catch (Exception $e) {
				echo "✗ DIAGNOSTIC FAILED: " . $e->getMessage() . "\n";
				echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
			}
			
			$diagnostic_output = ob_get_clean();
			$page->success = "Diagnostic completed successfully";
		}
	}
	
	$page->title("S3 Diagnostic Tool");
	$page->instructions = "This diagnostic tool will test all aspects of your S3 configuration and connectivity.";
?>