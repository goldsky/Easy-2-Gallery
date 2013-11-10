<?php

class ModuleSynchroProcessor extends E2GProcessor {

    public function process() {
        $userId = $this->config['uid'];

        $synchro = $this->modx->e2gMod->synchro('../../../../../' . $this->config['path'], $this->config['pid'], $userId);
        if ($synchro !== TRUE) {
            $output = $this->modx->e2gMod->getError();
        } else {
            $output = '<div class="success" style="padding-left: 10px;">' . $this->modx->e2gMod->lng['synchro_suc'] . '</div>';
        }

        return $output;
    }

}

return 'ModuleSynchroProcessor';
