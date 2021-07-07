# Welcome to the scripted section of Spectros Instruments.

Using python3 on your local machine you can run these scripts individually to produce automated API calls to populate data / add new readings and so on.

Use the syntax as follows for example

`$ python3 addSensor.py`

Note this folder will operate as a single module so resources will be shared among all scripts.

### Configuration
please use config.py to include any shared configuration values between scripts, generally if you

`import session` 
it will
`import config` 
along with it.  

Most scripts require authenitcation to perform the write actions anyway.
