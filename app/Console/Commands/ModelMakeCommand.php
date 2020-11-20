<?php
/**
 * Created by PhpStorm.
 * @author tann1013@hotmail.com
 * @date 2020-04-26
 * @version 1.0
 */

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ModelMakeCommand extends GeneratorCommand {
    /**
     * create a user defined controller.
     *
     * @var string
     */
    protected $name = 'make:model';  // @todo:要添加的命令

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new lumen model '; // @todo: 命令描述

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';  // command type

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub() {
        return dirname(__DIR__) . '/stubs/model.stub';  // @todo: 要生成的文件的模板
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace) {
        return $rootNamespace;//@todo：这里是定义要生成的类的命名空间
    }
}