<?php

/**
 * The Router class.
 *
 * Simplifies the process of handling incoming requests and directing to the
 * developer-defined code flow.
 *
 * @author Angel Garcia <git@angelgarcia.dev>
 */

namespace Perritu\Router;

use Exception, ReflectionClass;

/**
 * The Router class.
 *
 * Handles incoming requests and perform code calls based in the routes
 * definitions provided in the code flow.
 */
class Router
{
    // Bitwise constants
    // HTTP request method
    public const ANY     = 127; # Bits 1 to 7
    public const DELETE  = 1;   # Bit 1
    public const GET     = 2;   # Bit 2
    public const HEAD    = 4;   # Bit 3
    public const OPTIONS = 8;   # Bit 4
    public const PATCH   = 16;  # Bit 5
    public const POST    = 32;  # Bit 6
    public const PUT     = 64;  # Bit 7

    // Matching evaluation mode
    public const CASE_I = 1; # 001
    public const FLAT   = 2; # 010
    public const PREG   = 4; # 100
    public const IFLAT  = 3; # 011
    public const IPREG  = 5; # 101

    /**
     * @var string $Path Path requested by the current run.
     */
    public static ?string $Path = null;

    /**
     * @var string $Method Request method used by the current run.
     */
    public static ?string $Method = null;

    /**
     * @var int $MethodBit Bitwise representation of the request method.
     */
    public static int $MethodBit = 0;

    /**
     * @var string $CriteriaPrefix Prefix used to build the criteria string.
     */
    public static string $CriteriaPrefix = '';

    /**
     * @var string $ClassPrefix Prefix to apply to criteria during class matching.
     */
    public static string $ClassPrefix = '';

    /**
     * Initializes the router context.
     *
     * @param string $Path The request path. By default, it will use the
     *   `$_SERVER['REQUEST_URI']` value.
     * @param string $Method The request method. By default, it will use the
     *   `$_SERVER['REQUEST_METHOD']` value.
     * @return self
     */
    public static function init(?string $Path = null, ?string $Method = null): string
    {
        self::$Path = $Path ?? explode('?', $_SERVER['REQUEST_URI'])[0];
        self::$Method = \strtoupper($Method ?? $_SERVER['REQUEST_METHOD']);
        self::$MethodBit = match (self::$Method) {
            'DELETE'  => self::DELETE,
            'GET'     => self::GET,
            'HEAD'    => self::HEAD,
            'OPTIONS' => self::OPTIONS,
            'PATCH'   => self::PATCH,
            'POST'    => self::POST,
            'PUT'     => self::PUT,
            default   => throw new Exception('Invalid HTTP verb.', -1)
        };

        return self::class;
    }

    /**
     * Initializes the router context. Object oriented version.
     *
     * @param string $Path The request path. By default, it will use the
     *   `$_SERVER['REQUEST_URI']` value.
     * @param string $Method The request method. By default, it will use the
     *   `$_SERVER['REQUEST_METHOD']` value.
     */
    public function __construct(?string $Path = null, ?string $Method = null)
    {
        self::init($Path, $Method);
    }


    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param int $MethodBit Bitwise representation of the desired HTTP method to be handled.
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function MATCH(
        int $MethodBit,
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        if (self::$Path === null) self::init();

        $Criteria = \is_array($Criteria) ? $Criteria : [$Criteria, self::IFLAT];

        if (!($MethodBit & self::$MethodBit)) return null;

        [$Criteria, $Flags] = $Criteria;
        $Path = self::$Path;

        if ($Flags & self::FLAT) {
            if ($Flags & self::CASE_I) {
                $Criteria = \strtolower($Criteria);
                $Path = \strtolower($Path);
            }

            if ($Criteria === $Path) return self::PerformCall($Callback, $Terminate);
        }

        if ($Flags & self::PREG) {
            if ($Flags & self::CASE_I) {
                $Criteria = "(?i)$Criteria";
            }

            if (false !== preg_match("/$Criteria/", $Path, $Matches)) {
                unset($Matches[0]);
                return self::PerformCall($Callback, $Terminate, array_values($Matches));
            }
        }

        return null;
    }

    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function ANY(
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        return self::MATCH(self::ANY, $Criteria, $Callback, $Terminate);
    }

    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function DELETE(
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        return self::MATCH(self::DELETE, $Criteria, $Callback, $Terminate);
    }

    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function GET(
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        return self::MATCH(self::GET, $Criteria, $Callback, $Terminate);
    }

    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function HEAD(
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        return self::MATCH(self::HEAD, $Criteria, $Callback, $Terminate);
    }

    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function OPTIONS(
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        return self::MATCH(self::OPTIONS, $Criteria, $Callback, $Terminate);
    }

    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function PATCH(
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        return self::MATCH(self::PATCH, $Criteria, $Callback, $Terminate);
    }

    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function POST(
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        return self::MATCH(self::POST, $Criteria, $Callback, $Terminate);
    }

    /**
     * Launch the defined callback if given criteria matches the request.
     *
     * @param string|array $Criteria Criteria to be used.
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   calling `$Callback`.
     *
     * @return mixed The return value of the callback.
     */
    public static function PUT(
        string|array $Criteria,
        callable|string|array $Callback,
        bool $Terminate = true
    ): mixed {
        return self::MATCH(self::PUT, $Criteria, $Callback, $Terminate);
    }

    /**
     * Use a given namespace to handle the request in given conditions.
     *
     * @param string $Namespace Namespace to be used.
     * @param string $MountPoint Mount point to be used.
     * @param int $MethodBit Bitmask of the HTTP methods to be handled.
     * @param bool $Terminate If true, the ejection will be terminated after
     *   the first callback.
     */
    public static function USE(
        string $Namespace,
        string $MountPoint,
        int $MethodBit = self::ANY,
        bool $Terminate = true
    ): void {
        if (self::$MethodBit === null) self::init();
        if (!self::$MethodBit & $MethodBit) return; // No match, do nothing.

        $pointLength = strlen($MountPoint);
        $Point = \substr(self::$Path, $pointLength);

        // If no match, do nothing.
        if (\substr(self::$Path, 0, $pointLength) !== $MountPoint) return;

        // Check if there's a class for the namespace that matches the point.
        $Class = "\\$Namespace\\$Point";
        if (!class_exists($Class)) return; // No match, do nothing.

        // For the matched class, check if there's a method that matches the
        // http method requested.
        $Method = \strtoupper(self::$Method);
        $Reflector = new \ReflectionClass($Class);
        if (!$Reflector->hasMethod($Method)) return; // No match, do nothing.

        // For last, forward the request to the matched class.
        $oldClassPrefix = self::$ClassPrefix;
        self::$ClassPrefix = '';
        self::PerformCall([$Class, $Method], $Terminate);
        self::$ClassPrefix = $oldClassPrefix;
        // Note: By resetting the class prefix, the call forwarded matches with
        //   the right forwarded class.
    }

    /**
     * Perform the callback call. Internal use only.
     *
     * @param callable|string|array $Callback String or callable to be executed.
     * @param bool $Terminate If true, the ejection will be terminated after
     * @param array Arguments to be passed to the callback.
     */
    protected static function PerformCall(
        callable|string|array $Callback,
        bool $Terminate,
        array $Arguments = []
    ): mixed {
        if (\is_callable($Callback)) {
            $return = call_user_func_array($Callback, $Arguments);
            if ($Terminate) exit;
            return $return;
        }

        if (\is_array($Callback)) {
            $Callback = implode("::", $Callback);
        }

        // Apply class prefix and transformations.
        $Callback = self::$ClassPrefix . "\\$Callback";
        $Callback = preg_replace('/[\\\\\\/]+/', '\\', $Callback); // Literally, the Regex is: /[\\\/]+/
        $Callback = str_replace('@', '::', $Callback);
        [$Class, $Method] = explode("::", $Callback, 2);

        if (!class_exists($Class))
            throw new Exception("Class $Class not found.");

        $Reflector = new ReflectionClass($Class);
        if (!$Reflector->hasMethod($Method))
            throw new Exception("Method $Method not found in class $Class.");

        $ReflectorMethod = $Reflector->getMethod($Method);
        if (!$ReflectorMethod->isPublic())
            throw new Exception("Method $Method in class $Class is not public.");

        $ChildClass = new $Class;
        $return = $ReflectorMethod->invokeArgs($ChildClass, $Arguments);
        if ($Terminate) exit;
        return $return;
    }
}
