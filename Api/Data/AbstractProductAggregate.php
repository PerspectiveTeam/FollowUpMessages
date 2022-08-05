<?php

namespace Perspective\FollowUpMessages\Api\Data;

interface AbstractProductAggregate
{

    public function process(array $products);
}
