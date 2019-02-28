<?php

namespace RedAnt\TwigComponents;

use RedAnt\TwigComponents\TokenParser\ComponentTokenParser;
use Twig;

/**
 * Defines the Twig extensions for Components.
 *
 * Specifically, the 'render_component' function and the 'component' global,
 * which can be renamed or turned off.
 *
 * @author Gert Wijnalda <gert@redant.nl>
 */
class Extension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var Registry
     */
    protected $componentRegistry;

    /**
     * @var string|bool
     */
    protected $globalVariable;

    /**
     * Create a new ComponentExtension for Twig.
     *
     * You can add a registry of components, and set the Twig global through which components
     * can be accessed (or false if you do not want a global to be inserted into your Twig
     * environment).
     *
     * @param Registry    $componentRegistry
     * @param string|bool $globalVariable
     */
    public function __construct(Registry $componentRegistry, $globalVariable = 'component')
    {
        $this->componentRegistry = $componentRegistry;
        $this->globalVariable = $globalVariable;
    }

    /**
     * Returns the token parser instance to parse the 'component' tag, to add to the existing list.
     *
     * @return \Twig_TokenParserInterface[]
     */
    public function getTokenParsers()
    {
        return [
            new ComponentTokenParser()
        ];
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        $globals = [];

        if ($this->globalVariable !== false) {
            $globals[$this->globalVariable] = $this->componentRegistry;
        }

        return $globals;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $components = $this->componentRegistry->getComponents();

        $functions = [
            new \Twig_SimpleFunction('render_component',
                function (\Twig_Environment $env, string $componentName, array $options = []) use ($components) {
                    if (!array_key_exists($componentName, $components)) {
                        throw new Twig\Error\RuntimeError(
                            sprintf('Component "%s" does not exist.', $componentName));
                    }

                    return $env->render($components[$componentName], [ 'options' => $options ]);
                },
                [ 'needs_environment' => true, 'is_safe' => [ 'html' ] ])
        ];

        return $functions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twig_component';
    }
}
