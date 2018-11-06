<?php

namespace eArc\eventTree\Interfaces;


interface PropagationHandler
{
    public function silencePropagation(): void;

    public function isSilenced(): bool;

    public function endSilence(): void;

    public function terminateSelf(): void;

    public function isSelfTerminated(): bool;

    public function terminateOthers(): void;

    public function areOthersTerminated(): bool;

    public function endTermination(): void;
}