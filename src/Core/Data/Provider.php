<?php

namespace Core\Data;

interface Provider
{

    public function provide(): \Generator;
}