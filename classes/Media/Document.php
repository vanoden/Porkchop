<?
	namespace Media;

	class Document extends \Media\File {
		public function add($parameters = array()) {
			$document = parent::add(array("type" => 'document'));
			parent::setMeta($document->id,"name",$parameters['name']);
			return parent::details($document->id);
		}
		public function update($id,$parameters = array()) {
			if ($parameters['name']) {
				parent::setMeta($document->id,"name",$parameters['name']);
			}
			return parent::details($document->id);
		}
	}
?>