<?php

class ModuleCountfilesProcessor extends E2GProcessor {

    public function process() {
        $path = $this->config['path'];
        
        return $this->modx->e2gMod->countFiles($path);
    }

}

return 'ModuleCountfilesProcessor';
