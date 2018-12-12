<?php
/**
 * @purpose: 任务供应商
 * @author: NedRen<ned@pproject.co>
 * @date: 2018/10/26
 * @version: 1.0
 */


namespace Apps\Providers;

use Phalcon\CLI\Task;

abstract class TaskProvider extends Task
{
    public function mainAction()
    {
        echo "\nThis is the default task and the default action \n";
    }
}