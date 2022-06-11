<?php

namespace IOguns\SimpleView;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class View implements IView
{
    use LoggerAwareTrait;

    //String of template file
    private ?string $file = null;

    //Array of view sections
    private array $blocks = [];

    //Container for capture content for a section
    private array $capture = [];

    private array $dirs = [];

    private array $data = [];

    private int $level = 0;

    private string $content = '';

    /**
     * @params array $dirs Initial directory to get the views
     */
    function __construct(array $dirs = [])
    {
        $this->setDirectories($dirs);
        $this->logger = new NullLogger;
    }

    /**
     * Set search directory for the view
     *
     * @param  string $dir
     * @return IView
     */
    public function setDirectory(string $dir): IView
    {
        clearstatcache(true);
        $dir = realpath($dir);

        if (is_dir($dir)) {
            $this->dirs[$dir] = true;
        }
        return $this;
    }

    /**
     * Set search directories for the view
     *
     * @param array $dirs
     * @return IView view object
     */
    public function setDirectories(array $dirs): IView
    {
        foreach ($dirs as $dir) {
            $this->setDirectory($dir);
        }

        return $this;
    }

    public function getDirectories(): array
    {
        return $this->dirs;
    }

    /**
     * Set data
     * @param  string $key
     * @param  mixed  $value
     * @return IView view object
     */
    public function setData(string $key, $value): IView
    {
        return $this->populate([$key => $value]);
    }

    /**
     * Populate the view object 
     * @param array $data
     * @return IView view object
     */
    public function populate(array $data): IView
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * Get a file from the registered directories
     * @param  string $fileName
     * @return string
     */
    private function getFile(string $fileName): ?string
    {
        if (!str_ends_with($fileName, '.php')) {
            $fileName .= '.php';
        }

        $file = null;
        foreach ($this->dirs as $dir => $_) {
            if (file_exists($filePath = $dir . DIRECTORY_SEPARATOR . $fileName)) {
                $file = $filePath;
                unset($filePath);
                break;
            }
        }

        if (!$file) {
            $this->logger->warning("View file '{$fileName}' could not found.");
            return null;
        }

        return $file;
    }

    /**
     * Set the view to render
     *
     * @param string $file filename of the view to render
     * @return IView view object
     */
    public function setView(string $file): IView
    {
        //set the file
        $this->file = $this->getFile($file);
        return $this;
    }

    /**
     * Get the current session view file
     * @return string|null
     */
    public function getCurrentView(): ?string
    {
        return $this->file;
    }

    /**
     * Returns the output of a parsed template as a string.
     *
     * @return string Content of parsed template.
     */
    public function render(): string
    {
        if (is_null($this->file)) {
            $this->logger->error('View file is not yet set.');
            return '';
        }

        // start and infinite loop
        while (true) {
            // start capturing the contents
            ob_start();
            //include the view file
            include $this->file;

            //if it does not have a parent or layout return the content and end the loop
            if (!isset($this->parent)) {
                //return the captured content
                return ob_get_clean();
            }
            //the view has layout
            else {
                //increase the level
                $this->level++;
                //set the layout as the current view file
                $this->file = $this->parent;
                //unset set
                unset($this->parent);
                //svae the captured content to variable
                $this->content .= ob_get_clean();
            }
        }
    }

    /**
     * Get the main content of the view after processing
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * set parent layout
     *
     * @param string $parent
     */
    protected function setParent(string $parent): IView
    {
        $this->parent = $this->getFile($parent);
        return $this;
    }

    /**
     * Is a particular named block available?
     *
     * @param string $name the block name.
     *
     * @return bool
     */
    public function hasBlock(string $name): bool
    {
        return isset($this->blocks[strtolower(trim($name))]);
    }

    /**
     * Get a named block content.
     *
     * @param string $name
     *
     * @return string
     */
    public function getBlockContent(string $name): string
    {
        return $this->blocks[strtolower(trim($name))] ?? '';
    }

    /**
     * Set a body for a named block.
     *
     * @param string $name
     * @param string $body
     */
    private function setBlock(string $name, string $body): void
    {
        $this->blocks[strtolower(trim($name))] = $body;
    }

    /**
     * Begin a named block.
     *
     * @param string $name
     * @return bool
     */
    public function startBlock(string $name, ...$callbacks): bool
    {
        $this->capture[] = [strtolower(trim($name)), $callbacks];
        //start capturing the content after this function
        return ob_start();
    }

    /**
     * End a named block.
     */
    public function endBlock(): void
    {
        if (empty($this->capture)) {
            $this->logger->warning('You must start a block before you can end it.');
            return;
        }

        //get the content of the captured block
        $content = ob_get_clean();

        if ($content === false) {
            $this->logger->warning('No active block. Start a block before you can end it.');
            return;
        }

        //get the last content that was captured
        $info = array_pop($this->capture);

        //loop though and apply filters
        if (is_array($info[1])) {
            foreach ($info[1] as $filter) {
                $content = call_user_func($filter, $content);
            }
        }

        //set the named block if the name already exists combine the block contents
        if ($this->hasBlock($info[0])) {
            $previousContent = $this->getBlockContent($info[0]);
            $this->setBlock($info[0], $previousContent . $content);
        } else {
            $this->setBlock($info[0], $content);
        }
    }

    /**
     * Partially render a view
     * @param string $file
     * @param array  $data
     * @param array $dirs
     */
    public function partial(string $file, array $data = [], array $dirs = []): string
    {
        $view = new self;
        $view->setDirectories(array_merge(array_keys($this->dirs), $dirs))
            ->setView($file)
            ->populate($data);
        return $view->render();
    }

    // return this object as string
    public function __toString()
    {
        return $this->render();
    }
}
