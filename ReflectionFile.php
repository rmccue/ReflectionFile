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
		$depth = 0;
		$location = 'global';
		$entered_class_at = 0;
		$entered_func_at = 0;

		// Step 1: Get the file DocBlock
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

		// Step 2: Use the tokens to grab the classes, etc.
		foreach ($tokens as $token) {
			if (is_array($token)) {
				list($type, $value, $line) = $token;
			}
			else {
				if ($token === '{') {
					$depth++;
				}
				elseif ($token === '}') {
					$depth--;
					if ($depth === $entered_class_at) {
						//We're now out of the class definition
						$entered_class_at = 0;
						$location = 'global';
					}
					elseif ($depth === $entered_func_at) {
						$entered_func_at = 0;
						$location = 'global';
					}
				}
				continue;
			}
			switch ($type) {
				case T_STRING:
					if ($next === 'class') {
						$this->classes[] = $value;
						$next = false;
						$entered_class_at = $depth;
						$location = 'class';
					}
					elseif ($next === 'function') {
						$this->functions[] = $value;
						$next = false;
						$entered_func_at = $depth;
						$location = 'function';
					}
					break;
				case T_CLASS:
					$next = 'class';
					break;
				case T_FUNCTION:
					if ($location === 'global') {
						$next = 'function';
					}
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

	/**
	 * Retrieve the defined classes for a file
	 *
	 * @return array Class names
	 */
	public function getClasses() {
		return $this->classes;
	}

	/**
	 * Retrieve the file docblock
	 *
	 * @return string
	 */
	public function getDocComment() {
		return $this->docComment;
	}
}