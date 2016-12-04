<?php
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;
use Ulrichsg\Getopt\Argument;

class Options extends Getopt
{
    public function __construct()
    {
        parent::__construct([
            (new Option("r", "rotate", Getopt::REQUIRED_ARGUMENT))
                ->setDescription("How many snapshots should be saved")
                ->setArgument(new Argument(null, null, "count"))
                ->setDefaultValue(5)
                ->setValidation("is_numeric"),
            (new Option("p", "profile", Getopt::REQUIRED_ARGUMENT))
                ->setDescription("Name of profile written in ~/.aws/credentials")
                ->setArgument(new Argument(null, null, "name"))
                ->setDefaultValue("default"),
            (new Option("h", "help", Getopt::NO_ARGUMENT))
                ->setDescription("Show this message")
        ]);
        $this->setBanner("Usage: %s [options]\n");
    }
    
    public function parse($arguments = null)
    {
        parent::parse($arguments);
        if ($this->getOperands()) {
            throw new UnexpectedValueException();
        }
    }
}
