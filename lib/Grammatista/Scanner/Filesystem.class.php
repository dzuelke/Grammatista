<?php

class GrammatistaScannerFilesystem extends FilterIterator implements IGrammatistaScanner
{
	/**
	 * @var        mixed[] An array of option values.
	 */
	protected $options = array();

	/**
	 * Constructor. Accepts an array of options.
	 *
	 * Available options:
	 *  - string   filesystem.path
	 *  - string   filesystem.ident.strip
	 *
	 * @param      mixed[] The options.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.1.0
	 */
	public function __construct(array $options)
	{
		if(!isset($options['filesystem.path'])) {
			throw new GrammatistaException('No path given for GrammatistaScannerFilesystem');
		}

		if(!isset($options['filesystem.ident.strip'])) {
			$options['filesystem.ident.strip'] = '#^' . preg_quote($options['filesystem.path'] . '/', '#') . '#';
		}

		$this->options = $options;

		$this->innerIterator = new RecursiveIteratorIterator(new GrammatistaScannerFilesystemRecursivedirectoryiterator($options), RecursiveIteratorIterator::LEAVES_ONLY | RecursiveIteratorIterator::CHILD_FIRST);

		parent::__construct($this->innerIterator);
	}

	/**
	 * Passes all calls to the inner iterator.
	 *
	 * @return     Iterator
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.1.0
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->innerIterator, $name), $args);
	}

	/**
	 * Get the inner iterator.
	 *
	 * @return     Iterator
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.1.0
	 */
	public function getInnerIterator()
	{
		return $this->innerIterator;
	}

	/**
	 * Check whether the current element of the iterator is acceptable.
	 *
	 * @return     bool
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.1.0
	 */
	public function accept()
	{
		return $this->innerIterator->isFile();
	}

	/**
	 * Return the current element.
	 *
	 * @return     GrammatistaEntity The current element.
	 *
	 * @author     David Zülke <david.zuelke@bitextender.com>
	 * @since      0.1.0
	 */
	public function current()
	{
		$current = $this->innerIterator->current();

		$retval = new GrammatistaEntity(array(
			'ident' => preg_replace($this->options['filesystem.ident.strip'], '', $current->getRealpath()),
			'type' => pathinfo($current->getPathname(), PATHINFO_EXTENSION),
			'content' => file_get_contents($current->getRealpath()),
		));

		return $retval;
	}
}

?>