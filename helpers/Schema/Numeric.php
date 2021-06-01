<?php
/** @noinspection ContractViolationInspection */
declare(strict_types=1);

namespace noirapi\helpers\Schema;


use Nette\Schema\Context;
use Nette\Schema\Message;
use Nette\Schema\Schema;

class Numeric implements Schema {

    /** @var bool */
    private $required = false;
    /** @var bool */
    private $nullable = false;

    /** @var array{?int, ?int} */
    private $range = [null, null];

    public function required(bool $state = true): self {
        $this->required = $state;
        return $this;
    }

    public function nullable(bool $state = true): self {
        $this->nullable = $state;
        return $this;
    }

    /**
     * @param int $min
     * @return $this
     */
    public function min(int $min): Numeric {
        $this->range[0] = $min;
        return $this;
    }

    /**
     * @param int $max
     * @return $this
     */
    public function max(int $max): Numeric {
        $this->range[1] = $max;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function normalize($value, Context $context) {

        /** @noinspection TypeUnsafeComparisonInspection */
        if($this->nullable && (empty($value) && $value != '0')) {
            return null;
        }

        /** @noinspection TypeUnsafeComparisonInspection */
        if($this->required && (empty($value) && $value != '0')) {
            /** @noinspection UnusedFunctionResultInspection */
            $context->addError('The mandatory option %path% is empty.', Message::MISSING_ITEM);
            return null;
        }

        if(is_string($value) && !preg_match('/\d+/', $value)) {
            /** @noinspection UnusedFunctionResultInspection */
            $context->addError('The mandatory option %path% is string but not number.', Message::PATTERN_MISMATCH);
            return null;
        }
        $value = (int) $value;

        if(!$this->isInRange($value, $this->range)) {
            /** @noinspection UnusedFunctionResultInspection */
            $context->addError(
                'The %label% %path% expects to be in range %expected%, %value% given.',
                Message::VALUE_OUT_OF_RANGE,
                ['value' => $value, 'expected' => implode('..', $this->range)]
            );
            return false;
        }

        return $value;

    }

    /**
     * @inheritDoc
     */
    public function merge($value, $base) {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function complete($value, Context $context) {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function completeDefault(Context $context) {
        if ($this->required) {
            /** @noinspection UnusedFunctionResultInspection */
            $context->addError('The mandatory option %path% is missing.', Message::MISSING_ITEM);
        }
        return null;
    }

    private function isInRange($value, array $range): bool
    {
        return ($range[0] === null || $value >= $range[0])
            && ($range[1] === null || $value <= $range[1]);
    }

}
