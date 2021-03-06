<?php

namespace Lmc\Steward\Test;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Lmc\Steward\ConfigProvider;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test case to be used by all test cases.
 * It adds logging, some common logic and assertions.
 */
abstract class AbstractTestCase extends TestCase
{
    use SyntaxSugarTrait;

    /** @var int|null Default width of browser window. Use null to disable setting default window size on startup. */
    public const BROWSER_WIDTH = 1280;
    /** @var int|null Default height of browser window. Use null to disable setting default window size on startup. */
    public const BROWSER_HEIGHT = 1024;

    /** @var RemoteWebDriver */
    public $wd;

    /** @var string Log appended to output of this test */
    protected $appendedTestLog;

    public function setUp()
    {
        if ($this->wd instanceof RemoteWebDriver && static::BROWSER_WIDTH !== null && static::BROWSER_HEIGHT !== null) {
            $this->wd->manage()->window()->setSize(
                new WebDriverDimension(static::BROWSER_WIDTH, static::BROWSER_HEIGHT)
            );
        }
    }

    /**
     * Get output of current test. Parent method is overwritten to include also $appendedTestLog in the output
     * (called eg. from PHPUnit\Util\Log\JUnit).
     * @return string
     */
    public function getActualOutput()
    {
        $output = parent::getActualOutput();
        $output .= $this->appendedTestLog;

        return $output;
    }

    /**
     * Append given output at the end of test's log. This is useful especially when called from
     * Listeners, as the standard output won't be part of test output buffer.
     * @param string $format
     * @param mixed ...$args
     * @see log
     */
    public function appendTestLog($format, ...$args)
    {
        $output = $this->formatOutput($format, $args);
        $this->appendedTestLog .= $output;
    }

    /**
     * Append already formatted log (including timestamp, newlines etc.) to end of test's log.
     *
     * @param string $formattedLog
     * @see appendTestLog
     */
    public function appendFormattedTestLog($formattedLog)
    {
        $this->appendedTestLog .= $formattedLog;
    }

    /**
     * Log to output
     * @param string $format The format string. May use "%" placeholders, in a same way as sprintf()
     * @param mixed ...$args Variable number of parameters inserted into $format string
     */
    public function log($format, ...$args)
    {
        echo $this->formatOutput($format, $args);
    }

    /**
     * Log warning to output. Unlike log(), it will be prefixed with "WARN: " and colored.
     * @param string $format The format string. May use "%" placeholders, in a same way as sprintf()
     * @param mixed ...$args Variable number of parameters inserted into $format string
     */
    public function warn($format, ...$args)
    {
        echo $this->formatOutput($format, $args, 'WARN');
    }

    /**
     * Log to output, but only if debug mode is enabled.
     * @param string $format The format string. May use "%" placeholders, in a same way as sprintf()
     * @param mixed ...$args Variable number of parameters inserted into $format string
     */
    public function debug($format, ...$args)
    {
        if (ConfigProvider::getInstance()->debug) {
            echo $this->formatOutput($format, $args, 'DEBUG');
        }
    }

    /**
     * Sleep for given amount of seconds.
     * Unlike sleep(), also the float values are supported.
     * ALWAYS TRY TO USE WAIT() INSTEAD!
     * @param float $seconds
     */
    public static function sleep($seconds)
    {
        $fullSecond = (int) floor($seconds);
        $microseconds = fmod($seconds, 1) * 1000000000;

        time_nanosleep($fullSecond, $microseconds);
    }

    /**
     * Format output
     * @param string $format
     * @param array $args Array of arguments passed to original sprintf()-like function
     * @param string $type Specific log severity type (WARN, DEBUG) prefixed to output
     * @return string Formatted output
     */
    protected function formatOutput($format, array $args, $type = '')
    {
        // If first item of arguments contains another array use it as arguments
        if (!empty($args) && is_array($args[0])) {
            $args = $args[0];
        }

        return '[' . date('Y-m-d H:i:s') . ']'
            . ($type ? " [$type]" : '') . ' '
            . vsprintf($format, $args)
            . "\n";
    }
}
