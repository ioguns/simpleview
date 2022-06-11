<?php

namespace IOguns\SimpleView\Tests;

use IOguns\SimpleView\IView;
use IOguns\SimpleView\View;

class ViewTest extends \PHPUnit\Framework\TestCase
{
    private View $view;

    protected function setUp(): void
    {
        $this->view = new View();
    }

    public function testValidDirectories(): View
    {
        $this->view->setDirectories([__DIR__ . '/../examples/layouts/']);
        $this->assertContains(realpath(__DIR__ . '/../examples/layouts/'), array_keys($this->view->getDirectories()));
        return $this->view;
    }

    public function testInvalidDirectories()
    {
        $this->view->setDirectories([__DIR__ . '/../examples/layous/']);
        $this->assertNotContains(realpath(__DIR__ . '/../examples/layouts/'), array_keys($this->view->getDirectories()));
    }

    /**
     * @depends testValidDirectories
     */
    public function testSetInvalidView(IView $view)
    {
        $this->expectError();
        $view->setView('demo');
    }

    /**
     * @depends testValidDirectories
     */
    public function testSetValidView(IView $view)
    {
        $view->setView('parent');
        $this->assertEquals(
            $view->getCurrentView(),
            realpath(__DIR__ . '/../examples/layouts/') . '/parent.php'
        );

        return $view;
    }

    /**
     * @depends testSetValidView
     */
    public function testRenderView(IView $view)
    {
        $content = $view->render();
        $this->assertEquals(
            trim($content),
            'Parent'
        );

        return $view;
    }

    /**
     * @depends testValidDirectories
     */
    public function testSetParentAndChildView(IView $view)
    {
        $view->setView('child_1');
        $this->assertEquals(
            $view->getCurrentView(),
            realpath(__DIR__ . '/../examples/layouts/') . '/child_1.php'
        );

        return $view;
    }

     /**
     * @depends testSetValidView
     */
    public function testChildRenderView(IView $view)
    {
        $content = $view->render();
        $this->assertEquals(
            trim($content),
            "Parent\nMain\nBlock Main"
        );

        return $view;
    }

    /**
     * @depends testSetValidView
     */
    public function testEqualRenderView(IView $view)
    {
        $view->setView('child_1');
        var_dump($view);
        $this->assertEquals(
            $view->render(),
            $view->getContent()
        );

        return $view;
    }
}
