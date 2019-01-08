<?php
namespace Application\Service\Daemon;

abstract class SamsaTask implements \Core_ITask
{

    private $job;

    private $workspace;

    /**
     * Called on Destruct
     *
     * @return void
     */
    public function teardown()
    {
        $this->job->status = "stopped";
        $this->job->update();
    }
}

?>