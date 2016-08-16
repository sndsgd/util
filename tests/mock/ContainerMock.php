<?php

class ContainerMock extends \sndsgd\Container
{
    public function getConfig(): \sndsgd\Config
    {
        return $this->getSingleton("config", function() {
            return new \sndsgd\Config([]);
        });
    }

    public function resetConfig()
    {
        $this->resetSingleton("config", function($name, $instance) {
            echo "$name ".get_class($instance);
        });
    }
}
