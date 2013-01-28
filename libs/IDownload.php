<?php

namespace h4kuna;

interface IDownload {

    public function downloading();

    public function setDefault($code);
}
