<?php
namespace Phulner;

abstract class InjectorAbstract implements Injector {
    abstract public function inject ($code, $options);
}
