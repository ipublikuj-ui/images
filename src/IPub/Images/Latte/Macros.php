<?php
/**
 * Macros.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Images!
 * @subpackage     Latte
 * @since          1.0.0
 *
 * @date           05.04.14
 */

namespace IPub\Images\Latte;

use Nette;

use Latte;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;
use Latte\Macros\MacroSet;

use IPub;

/**
 * Latte macros
 *
 * @package        iPublikuj:Images!
 * @subpackage     Latte
 *
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 */
final class Macros extends MacroSet
{
	/**
	 * Register latte macros
	 *
	 * @param Compiler $compiler
	 *
	 * @return static
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);

		/**
		 * {src provider:storage://[namespace/$name[, $width, $height[, $algorithm]]}
		 */
		self::registerMacro('src', $me);
		self::registerMacro('img', $me);

		return $me;
	}

	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 *
	 * @return string
	 */
	public function macroSrc(MacroNode $node, PhpWriter $writer)
	{
		return $writer->write('echo %escape(%modify($template->getImagesLoaderService()->request(IPub\Images\Latte\Macros::prepareArguments([%node.args]))))');
	}

	/**
	 * @param array $macro
	 *
	 * @return array
	 */
	public static function prepareArguments(array $macro)
	{
		preg_match("/\b(?P<provider>[a-zA-Z]+)\:(?P<storage>[a-zA-Z]+)\:\/\/(?:(?<namespace>[a-zA-Z0-9-_\/]+)\/)?(?<name>[a-zA-Z0-9-_]+).(?P<extension>[a-zA-Z]{3}+)/i", $macro[0], $matches);

		$arguments = [
			'provider'  => isset($matches['provider']) ? $matches['provider'] : NULL,
			'storage'   => isset($matches['storage']) ? $matches['storage'] : NULL,
			'namespace' => isset($matches['namespace']) && trim(trim($matches['namespace']), '/') ? $matches['namespace'] : NULL,
			'filename'  => isset($matches['name']) && isset($matches['extension']) ? $matches['name'] . '.' . $matches['extension'] : NULL,
			'size'      => (isset($macro[1]) && !empty($macro[1])) ? $macro[1] : NULL,
			'algorithm' => (isset($macro[2]) && !empty($macro[2])) ? $macro[2] : NULL,
		];

		return $arguments;
	}

	/**
	 * @param string $name
	 * @param Macros $macros
	 */
	private static function registerMacro($name, Macros $macros)
	{
		$macros->addMacro($name, function (MacroNode $node, PhpWriter $writer) use ($macros) {
			return $macros->macroSrc($node, $writer);
		}, NULL, function (MacroNode $node, PhpWriter $writer) use ($macros) {
			return ' ?> ' . ($node->htmlNode->name === 'a' ? 'href' : 'src') . '="<?php ' . $macros->macroSrc($node, $writer) . ' ?>"<?php ';
		});
	}
}
