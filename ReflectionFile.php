<?php
/**
 * Hello file.
 */

/**
 * Hello class.
 */
class ReflectionFile implements Reflector {
	protected $filename;

	protected $docComment;

	protected $classes;

	protected $functions;

	public function __construct($filename) {
		$this->filename = $filename;
		$this->reflect();
	}
	protected function reflect() {
		$contents = file_get_contents($this->filename);
		$tokens = token_get_all($contents);
		$next = false;
		
		foreach ($tokens as $token) {
			if (!is_array($token)) {
				$this->docComment = '';
				break;
			}
			list($type, $value, $line) = $token;
			switch ($type) {
				case T_OPEN_TAG:
				case T_WHITESPACE:
					continue;
					break;
				case T_DOC_COMMENT:
					$this->docComment = $value;
					break 2;
				default:
					$this->docComment = '';
					break;
			}
		}
		
		foreach ($tokens as $token) {
			if (is_array($token)) {
				list($type, $value, $line) = $token;
			}
			else {
				continue;
			}
			switch ($type) {
				case T_STRING:
					if ($next === 'class') {
						$this->classes[] = $value;
						$next = false;
					}
					elseif ($next === 'function') {
						$this->functions[] = $value;
						$next = false;
					}
					break;
				case T_CLASS:
					$next = 'class';
					break;
				case T_FUNCTION:
					$next = 'function';
					break;
			}
		}
	}
	
	public function __toString() {
		return '';
	}
	
	/**
	 * Export (something)
	 *
	 * @internal Where is this used?
	 * @return null
	 */
	public static function export() {
		return null;
	}

	public function getClasses() {
		return $this->classes;
	}
	
	public function getDocComment() {
		return $this->docComment;
	}
}

$a = new ReflectionFile(__FILE__);
var_dump($a->getClasses());