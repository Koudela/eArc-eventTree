<?php

namespace eArc\eventTree\Traits;

trait PropagatableHandler
{
    protected $isSilenced = false;
    protected $isSelfTerminated = false;
    protected $areOthersTerminated = false;

    public function silencePropagation(): void
    {
        $this->isSilenced = true;
    }

    public function isSilenced(): bool
    {
        return $this->isSilenced;
    }

    public function endSilence(): void
    {
        $this->isSilenced = false;
    }

    public function terminateSelf(): void
    {
        $this->isSelfTerminated = true;
    }

    public function isSelfTerminated(): bool
    {
        return $this->isSelfTerminated;
    }

    public function terminateOthers(): void
    {
        $this->areOthersTerminated = true;
    }

    public function areOthersTerminated(): bool
    {
        return $this->areOthersTerminated;
    }

    public function endTermination(): void
    {
        $this->isSelfTerminated = false;
        $this->areOthersTerminated = false;
    }
}