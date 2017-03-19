<?php

class ContainerMock extends \sndsgd\Container
{
    public function getConfig(): \sndsgd\Config
    {
        return $this->get("config", function() {
            return new \sndsgd\Config([]);
        });
    }

    public function resetConfig()
    {
        $this->reset("config", function($name, $instance) {
            echo "$name ".get_class($instance);
        });
    }
}
