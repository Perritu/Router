<?php

/**
 * Router handles requests and perform code calls based in the routes
 * definitions.
 */

namespace Perritu\Router;

use Exception;

/**
 * The Router class.
 *
 * Simplifies the process of handling incoming requests and directing to the
 * developer-defined code flow.
 */
class Router
{
    /**
     * @var string $RequestPath Path requested by the current run.
     * @readonly
     */
    public static ?string $RequestPath = null;

    /**
     * @var string $RequestVerb HTTP verb requested by the current run.
     * @readonly
     */
    public static ?string $RequestVerb = null;

    /**
     * @var int $RequestVerbBitwise Bitwise representation of self::RequestVerb.
     * @readonly
     */
    public static int $RequestVerbBitwise;

    /**
     * @var string $ClassPrefix Prefix applied when calling method strings.
     */
    public static string $ClassPrefix = '';

    /**
     * @var string $CriteriaPrefix Prefix applied to criteria when evaluating.
     */
    public static string $CriteriaPrefix = '';

    /**
     * Bitwise operators for HTTP verbs
     */
    final public const ANY     = 0b1111111; # 127
    final public const DELETE  = 0b0000001; # 1
    final public const GET     = 0b0000010; # 2
    final public const HEAD    = 0b0000100; # 4
    final public const OPTIONS = 0b0001000; # 8
    final public const PATCH   = 0b0010000; # 16, 0x10
    final public const POST    = 0b0100000; # 32, 0x20
    final public const PUT     = 0b1000000; # 64, 0x40

    /**
     * Bitwise operators for ::Evaluate operations
     */
    final public const E_FLAT   = 0b001; # 1
    final public const E_FLAT_I = 0b101; # 5
    final public const E_PREG   = 0b010; # 2
    final public const E_PREG_I = 0b110; # 6

    /**
     * Bitwise operator for ::Evaluate internal operations
     */
    final protected const E_CASE_INSENSITIVE = 0b100; // 4

    /**
     * Inits a new instance and sets the path and HTTP verb to be used.
     *
     * @param string $Path The path presented by the request.
     * @param string $Verb The HTTP verb presented by the request.
     */
    public function __construct(string $Path = null, string $Verb = null)
    {
        if (null === $Path)
            $Path = self::IfNUll($_SERVER['REQUEST_URI'], null);

        if (null === $Verb)
            $Verb = self::IfNUll($_SERVER['REQUEST_METHOD'], null);

        if (null === $Path || null === $Verb)
            throw new Exception('Cannot fetch the request values.', -1);

        self::$RequestPath = $Path;
        self::$RequestVerb = $Verb;

        switch ($Verb) {
            case "DELETE":
                self::$RequestVerbBitwise = self::DELETE;
                break;

            case "GET":
                self::$RequestVerbBitwise = self::GET;
                break;

            case "HEAD":
                self::$RequestVerbBitwise = self::HEAD;
                break;

            case "OPTIONS":
                self::$RequestVerbBitwise = self::OPTIONS;
                break;

            case "PATCH":
                self::$RequestVerbBitwise = self::PATCH;
                break;

            case "POST":
                self::$RequestVerbBitwise = self::POST;
                break;

            case "PUT":
                self::$RequestVerbBitwise = self::PUT;
                break;

            default:
                exit;
        }
    }

    /**
     * Perform a request evaluation against the given parametters.
     *
     * @param string $Criteria Criteria to be used.
     * @param int    $Flags    Operation mode.
     * @return bool  `true` if the operation matches.
     */
    public static function Evaluate(string $Criteria, int $Flags)
    {
        // Reference for internal use
        $Reference = self::$CriteriaPrefix . $Criteria;

        if (0 !== (self::E_FLAT & $Flags)) { // Flat plain-text evaluation mode.
            if (0 !== (self::E_CASE_INSENSITIVE & $Flags))
                return strtolower($Reference) == strtolower(self::$RequestPath);

            return $Reference == self::$RequestPath;
        }

        if (0 !== (self::E_PREG & $Flags)) { // Regular expression evaluation mode.
            if (0 !== (self::E_CASE_INSENSITIVE & $Flags))
                $Reference = "(?i)$Reference";

            if (1 !== preg_match("/$Reference/", self::$RequestPath, $Matches))
                return false;

            return $Matches;
        }

        throw new \Exception("Flags is invalid", -1);
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param int    $Verb      Bitwise junction of desired HTTP verbs to be
     *                          handled.
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     *
     * @see function.evaluate.md
     */
    public static function MATCH(
        int $Verb,
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        // Trigger the constructor if not already.
        if (null === self::$RequestPath) new \Perritu\Router\Router();

        // If the current HTTP verb is NOT wanted, end function.
        if (0 === (self::$RequestVerbBitwise & $Verb)) return false;

        // Perform the matching proccess.
        if (false === ($Params = self::Evaluate($Criteria, $EvalFlags)))
            return false;

        // Once in this point, the call execution shall be performed.

        // Sanitize the `$Params` (by converting a possible `true` to array)
        if (false === is_array($Params)) $Params = [];

        // Perform the `$Callback` excecution.

        $Response = self::PerformCall($Callback, $Params);

        // If the result is an array, output it as an API response.
        if (is_array($Response)) {
            // If the requested content is known, output that way.
            if (self::IsApi()) {
                // ...
            }

            header('Content-type: application/json');
            echo json_encode($Response, JSON_UNESCAPED_UNICODE);
        }

        // Terminate the code execution.
        if ($Terminate)
            exit;

        return true;
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     * @see function.evaluate.md
     */
    public static function ANY(
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        return self::MATCH(
            self::ANY,
            $Criteria,
            $Callback,
            $EvalFlags,
            $Terminate
        );
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     * @see function.evaluate.md
     */
    public static function DELETE(
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        return self::MATCH(
            self::DELETE,
            $Criteria,
            $Callback,
            $EvalFlags,
            $Terminate
        );
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     * @see function.evaluate.md
     */
    public static function GET(
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        return self::MATCH(
            self::GET,
            $Criteria,
            $Callback,
            $EvalFlags,
            $Terminate
        );
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     * @see function.evaluate.md
     */
    public static function HEAD(
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        return self::MATCH(
            self::HEAD,
            $Criteria,
            $Callback,
            $EvalFlags,
            $Terminate
        );
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     * @see function.evaluate.md
     */
    public static function OPTIONS(
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        return self::MATCH(
            self::OPTIONS,
            $Criteria,
            $Callback,
            $EvalFlags,
            $Terminate
        );
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     * @see function.evaluate.md
     */
    public static function PATCH(
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        return self::MATCH(
            self::PATCH,
            $Criteria,
            $Callback,
            $EvalFlags,
            $Terminate
        );
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     * @see function.evaluate.md
     */
    public static function POST(
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        return self::MATCH(
            self::POST,
            $Criteria,
            $Callback,
            $EvalFlags,
            $Terminate
        );
    }

    /**
     * Perform code call when a request mathces with the given criteria.
     *
     * @param string $Criteria  String to be used in the matching process.
     * @param mixed  $Callback  String or callable to be executed.
     * @param int    $EvalFlags Bitwise to be used in the matching process.
     * @param bool   $Terminate If true, the ejection will be terminated after
     *                          calling `$Callback`.
     *
     * @return bool  true if `$Callback` was called.
     * @see function.evaluate.md
     */
    public static function PUT(
        string $Criteria,
        callable | string $Callback,
        int $EvalFlags = self::E_FLAT_I,
        bool $Terminate = true
    ): bool {
        return self::MATCH(
            self::PUT,
            $Criteria,
            $Callback,
            $EvalFlags,
            $Terminate
        );
    }

    /**
     * Perform a namespace-based evaluation for the request.
     *
     * @param string $BaseNamespace Path to the root namespace to be used.
     * @param string $BaseCriteria  Root path for evaluate the request.
     * @param int    $Verb          Bitwise representation of the desired HTTP verbs to be handled.
     * @param bool   $Terminate     If true, the ejection will be terminated after
     *                              calling `$Callback`.
     *
     * @return bool  true if a callback was performed.
     */
    public static function MountNamespace(
        string $BaseNamespace,
        string $BaseCriteria = '/',
        int $Verb = self::ANY,
        bool $Terminate = true
    ): bool {
        if (0 !== strpos(self::$RequestPath, $BaseCriteria)) return false;
        if (0 === self::$RequestVerbBitwise & $Verb) return false;

        $Path = substr(self::$RequestPath, strlen($BaseCriteria));
        $Full = preg_replace('/[\/\\\\]+/', '\\', "$BaseNamespace/$Path@" . self::$RequestVerb);

        [$Class, $Method] = explode('@', $Full, 2);

        if (!class_exists($Class)) return false;
        if (!method_exists($Class, $Method)) return false;

        return self::MATCH($Verb, '(.+)', $Full, self::E_PREG, $Terminate);
    }

    /**
     * Try to guess if the current request is expecting an API response.
     *
     * @return bool `true` if the current request is expecting an API response.
     */
    public static function IsApi(): bool
    {
        // If the header is ausent, drop this out.
        if (false === isset($_SERVER['HTTP_CONTENT_TYPE'])) return false;

        return in_array(
            strtolower($_SERVER['HTTP_CONTENT_TYPE']),
            [
                'application/json',
                'application/xml',
                'text/xml',
            ]
        );
    }

    ////////////////////////////////////////////////////////////////////////////
    // Private functions. Internal use only. ///////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////

    /**
     * Return the first parametter if is not null, the fallback if it's.
     *
     * @param mixed $Test     Value to be tested.
     * @param mixed $Fallback Fallback value to be returned.
     * @return mixed Return `$Fallback` if `$Test` is null, or `$Test` if not.
     */
    private static function IfNUll(&$Test, $Fallback)
    {
        return (null === $Test) ? $Fallback : $Test;
    }

    /**
     * Perform the call execution and return the result of it.
     *
     * @param callable $Callback Callable or string to method.
     * @param array    $Params   Array to pass as function params.
     * @return mixed   Result of the function execution.
     */
    private static function PerformCall(
        callable | string $Callback,
        array $Params
    ) {
        // Try to call it directly
        if (is_callable($Callback)) {
            try {
                return call_user_func_array($Callback, $Params);
            } catch (\Exception $E) {
                throw new \Exception('Callback excecution failed.', -1, $E);
            }
        }

        // If it's not a callable, there must be a string with an '@'.
        if (false === strpos($Callback, '@'))
            throw new \Exception('Callback is not valid.', -1);

        // Prepend the prefix to the string an assamle a new one.
        $Callback = preg_replace('/^\\\\+/', '\\',  self::$ClassPrefix . "\\$Callback");

        // Split the class path from the method.
        [$ClassPath, $MethodName] = explode('@', $Callback, 2);

        // The class and method must be real and reachable.
        if (false === class_exists($ClassPath))
            throw new \Exception('Given class does not exist.', -1);

        if (false === method_exists($ClassPath, $MethodName))
            throw new \Exception('Given method does not exist.', -1);

        // Create a reflector to see the method metadata.
        $Reflector = new \ReflectionMethod($ClassPath, $MethodName);

        if (false === $Reflector->isPublic())
            throw new \Exception('The method is not public.', -1);

        // Perform the call, then.
        try {
            // If it's static, call that way.
            if ($Reflector->isStatic())
                return forward_static_call_array(
                    [$ClassPath, $MethodName],
                    $Params
                );

            // It's not a satic method, so let's instance and call.
            $ClassInstance = new $ClassPath();
            return call_user_func_array(
                [$ClassInstance, $MethodName],
                $Params
            );
        } catch (\Exception $E) {
            throw new \Exception('Callback excecution failed.', -1, $E);
        }
    }
}
