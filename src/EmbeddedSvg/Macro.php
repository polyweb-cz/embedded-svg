<?php

namespace PolywebCz\EmbeddedSvg;

use DOMDocument;
use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class Macro extends MacroSet
{

    /** @var MacroSetting */
	private $setting;

	public function __construct(Compiler $compiler, MacroSetting $setting)
	{
		if (!extension_loaded('dom')) {
			throw new \LogicException('Missing PHP extension xml.');
		} elseif (!is_dir($setting->baseDir)) {
			throw new CompileException("Base directory '$setting->baseDir' does not exist.");
		}

		parent::__construct($compiler);
		$this->setting = $setting;
	}


	public static function install(Compiler $compiler, MacroSetting $setting)
	{
		$me = new static($compiler, $setting);
		$me->addMacro($setting->macroName, [$me, 'open']);
	}


	public function open(MacroNode $node, PhpWriter $writer)
	{
		$file = $node->tokenizer->fetchWord();

        if ($file === false) {
            throw new CompileException('Missing SVG file path.');
        }

        if (substr($file, 0, 1) !== '$') {
            /**
             * $file is not just a variable, treat as a direct path
             */
            $file = trim($file, '\'"');
            $file = "'$file'";
        }

        $macroAttributes = $writer->formatArray();

        $setting = [
            'baseDir' => $this->setting->baseDir,
            'macroName' => $this->setting->macroName,
            'libXmlOptions' => $this->setting->libXmlOptions,
            'prettyOutput' => $this->setting->prettyOutput,
            'defaultAttributes' => $this->setting->defaultAttributes,
            'onLoad' => $this->setting->onLoad,
        ];

		return $writer->write(
            'echo %0.raw::includeSvgContent(%1.var, (%2.raw), %3.raw);',
			self::class,
            $setting,
            $file,
            $macroAttributes
		);
	}

	public static function includeSvgContent($setting, $file, $attributes)
    {
	    $setting = (object) $setting;

        $path = $setting->baseDir . DIRECTORY_SEPARATOR . trim($file, '\'"');
        if (!is_file($path)) {
            throw new CompileException("SVG file '$path' does not exist.");
        }

        XmlErrorException::try();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        @$dom->load($path, $setting->libXmlOptions);  // @ - triggers warning on empty XML
        if ($e = XmlErrorException::catch()) {
            throw new CompileException("Failed to load SVG content from '$path'.", 0, $e);
        }
        foreach ($setting->onLoad as $cb) {
            $cb($dom, $setting);
        }

        if (strtolower($dom->documentElement->nodeName) !== 'svg') {
            throw new CompileException("Sorry, only <svg> (non-prefixed) root element is supported but {$dom->documentElement->nodeName} is used. You may open feature request.");
        }

        $svgAttributes = [
            'xmlns' => $dom->documentElement->namespaceURI,
        ];
        foreach ($dom->documentElement->attributes as $attribute) {
            $svgAttributes[$attribute->name] = $attribute->value;
        }

        $inner = '';
        $dom->formatOutput = $setting->prettyOutput;
        foreach ($dom->documentElement->childNodes as $childNode) {
            $inner .= $dom->saveXML($childNode);
        }

        $svg = '<svg';

        foreach ($attributes + $setting->defaultAttributes + $svgAttributes as $n => $v) {
            if ($v === null || $v === false) {
                continue;
            } elseif ($v === true) {
                $svg .= ' ' . htmlspecialchars($n);
            } else {
                $svg .= ' ' . htmlspecialchars($n) . '="' . htmlspecialchars($v) . '"';
            }
        }

        $svg .= '>' . $inner . '</svg>';

        return $svg;
    }

}
