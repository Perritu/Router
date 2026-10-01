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

use Exception, InvalidArgumentException, RuntimeException, ReflectionClass;
use Uri\Rfc3986\Uri;

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
  public const ANY     = 255; # Bits 1 to 8
  public const DELETE  = 1;   # Bit 1
  public const GET     = 2;   # Bit 2
  public const HEAD    = 4;   # Bit 3
  public const OPTIONS = 8;   # Bit 4
  public const PATCH   = 16;  # Bit 5
  public const POST    = 32;  # Bit 6
  public const PUT     = 64;  # Bit 7
  public const QUERY   = 128; # Bit 8

  // Matching evaluation mode
  public const CASE_I = 1; # 001
  public const FLAT   = 2; # 010
  public const PREG   = 4; # 100
  public const IFLAT  = 3; # 011
  public const IPREG  = 5; # 101

  /**
   * @var string Path requested by the current run.
   */
  public static ?string $Path = null;

  /**
   * @var string Request method used by the current run.
   */
  public static ?string $Method = null;

  /**
   * @var int Bitwise representation of the request method.
   */
  public static int $MethodBit = 0;

  /**
   * @var string Prefix used to build the criteria string.
   */
  public static string $CriteriaPrefix = '';

  /**
   * @var string Prefix to apply to criteria during class matching.
   */
  public static string $ClassPrefix = '';

  /**
   * @var bool Flag to prevent any execution if a successfull callback has been performed.
   */
  public protected(set) static bool $PreventCallback = false;

  /**
   * Forwards a callback to the specified target, handling string, array, or callable inputs.
   *
   * @param callable|string|array $cCallback The callback to be executed. Can be a fully qualified class name and method name (as a string), an array of [class, method] names, or a callable.
   * @param array $aArguments Arguments to pass to the callback.
   * @return mixed The result of the callback execution.
   * @throws InvalidArgumentException If an array callback does not contain exactly two string elements.
   * @throws RuntimeException If the callback points to a non-existent class, a method is not found, or if invocation fails.
   */
  protected static function ForwardCallback(callable|string|array $cCallback, array $aArguments = []): mixed
  {
    if (is_string($cCallback) && preg_match(
      '/^((?:\\\\?[a-z_\x7f-\xff][a-z_\d\x7f-\xff]*)+)(?:::|@)([a-z_\x7f-\xff][\da-z_\x7f-\xff]*)$/i',
      $cCallback,
      $aMatches
    ) === 1) {
      [, $cClass, $cMethod] = $aMatches;

      // Add the class prefix.
      if ($cClass[0] !== '\\' && !empty(static::$ClassPrefix)) {
        $cClass = rtrim(static::$ClassPrefix, '\\') . '\\' . $cClass;
      }

      $cCallback = [$cClass, $cMethod];
    }

    if (is_array($cCallback)) {
      if (count($cCallback) !== 2)
        throw new InvalidArgumentException("Callback as array must contain exactly 2 string elements.");

      [$cClass, $cMethod] = array_values($cCallback);

      if (!is_string($cClass) || !is_string($cMethod))
        throw new InvalidArgumentException("Callback as array must contain exactly 2 string elements.");

      if (!class_exists($cClass))
        throw new RuntimeException("\$cCallback points to a non-existent class: '$cClass'");

      $rfReflectionClass = new ReflectionClass($cClass);
      if (!$rfReflectionClass->hasMethod($cMethod))
        throw new RuntimeException("Failed to find method '$cMethod' on class '$cClass'.");

      $rfReflectionMethod = $rfReflectionClass->getMethod($cMethod);
      if (!$rfReflectionMethod->isPublic())
        throw new RuntimeException("Failed to invoke '$cMethod' on class '$cClass' as it is non-public.");

      if (!$rfReflectionMethod->isStatic())
        $cCallback[0] = new $cClass();
    }

    if (is_callable($cCallback))
      return call_user_func_array($cCallback, $aArguments);

    throw new RuntimeException("Failed to invoke callback due to unexpected flow or invalid callable.");
  }

  /**
   * Initializes the router with request details and sets static properties.
   *
   * @param ?string $cPath The requested path.
   * @param ?string $cMethod The HTTP method.
   * @param ?string $cHost The host.
   * @param ?int    $uPort The port.
   */
  public function __construct(
    ?string $cPath = null,
    ?string $cMethod = null,
    ?string $cHost = null,
    ?int $uPort = null
  ) {
    static::init($cPath, $cMethod, $cHost, $uPort);
  }

  /**
   * Initializes the router with request details and sets static properties.
   *
   * @param ?string $cPath The requested path.
   * @param ?string $cMethod The HTTP method.
   * @param ?string $cHost The host.
   * @param ?int    $uPort The port.
   * @return string The class name of the router.
   */
  public static function init(
    ?string $cPath = null,
    ?string $cMethod = null,
    ?string $cHost = null,
    ?int    $uPort = null
  ): string {
    $cPath   ??= $_SERVER['REQUEST_URI'];
    $cMethod ??= $_SERVER['REQUEST_METHOD'];
    $cHost   ??= $_SERVER['HTTP_HOST'];
    $uPort   ??= $_SERVER['SERVER_PORT'];

    $Uri = sprintf('%s://%s:%u/%s', $cMethod, $cHost, $uPort, ltrim($cPath, '/'));
    $Uri = new Uri($Uri);

    static::$Path = preg_replace('/(?:\/\.{1,2}\/|\/{2,})+/', '/', $Uri->getPath());
    static::$Method = strtoupper($Uri->getScheme());
    static::$MethodBit = match (static::$Method) {
      'DELETE'  => static::DELETE,
      'GET'     => static::GET,
      'HEAD'    => static::HEAD,
      'OPTIONS' => static::OPTIONS,
      'PATCH'   => static::PATCH,
      'POST'    => static::POST,
      'PUT'     => static::PUT,
      default   => throw new Exception('Invalid HTTP verb.', -1)
    };
    static::$PreventCallback = false;

    return static::class;
  }

  /**
   * Evaluates route criteria against the current path using flat or regex matching,
   * and optionally executes a callback if a match is found.
   *
   * @param int $bitMethod
   *   Bitwise flag indicating the HTTP method to check against `static::$MethodBit`.
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function MATCH(
    int $bitMethod,
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true,
  ): array|false {
    if (static::$MethodBit === 0) static::init();
    if ((static::$MethodBit & $bitMethod) === 0) return false;
    if (static::$PreventCallback) return false;

    if (is_string($cCriteria)) {
      $bitCriteria = static::IFLAT;
    } else { // is_array
      [$cCriteria, $bitCriteria] = $cCriteria;
      if (is_int($cCriteria)) // Swap values
        [$cCriteria, $bitCriteria] = [$bitCriteria, $cCriteria];

      if (!is_string($cCriteria)) {
        throw new InvalidArgumentException('Criteria has no strings to compare with.');
      }
    }

    if ((static::PREG & $bitCriteria) !== 0) {
      $cRegex = '/' . static::$CriteriaPrefix . $cCriteria . '/';
      if ((static::CASE_I & $bitCriteria) !== 0)
        $cRegex .= 'i';

      if (preg_match($cRegex, static::$Path, $aMatches) !== 1)
        return false;

      array_shift($aMatches); // Remove the first element as it contains the whole match, not the groups.
    } elseif ((static::FLAT & $bitCriteria) !== 0) {
      $cComparison = static::$CriteriaPrefix . $cCriteria;

      if ((static::CASE_I & $bitCriteria) !== 0) {
        if (strtolower(static::$Path) !== strtolower($cComparison))
          return false;
      } else {
        if (static::$Path !== $cComparison)
          return false;
      }

      $aMatches = [];
    } else {
      throw new InvalidArgumentException('$cCriteria does not meet FLAT nor PREG modes.');
    }

    if (is_null($cCallback)) return [true, $aMatches]; // Ejecution handled elsewhere.

    $mResult = static::ForwardCallback($cCallback, $aMatches);
    if ($bTerminate) static::$PreventCallback = true;
    return [true, $mResult];
  }

  /**
   * @alias `Router::MATCH(Router::ANY)`
   *
   * Evaluates route criteria against any HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function ANY(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::ANY, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * @alias `Router::MATCH(Router::DELETE)`
   *
   * Evaluates route criteria against DELETE HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function DELETE(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::DELETE, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * @alias `Router::MATCH(Router::GET)`
   *
   * Evaluates route criteria against GET HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function GET(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::GET, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * @alias `Router::MATCH(Router::HEAD)`
   *
   * Evaluates route criteria against HEAD HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function HEAD(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::HEAD, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * @alias `Router::MATCH(Router::OPTIONS)`
   *
   * Evaluates route criteria against OPTIONS HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function OPTIONS(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::OPTIONS, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * @alias `Router::MATCH(Router::PATCH)`
   *
   * Evaluates route criteria against PATCH HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function PATCH(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::PATCH, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * @alias `Router::MATCH(Router::POST)`
   *
   * Evaluates route criteria against POST HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function POST(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::POST, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * @alias `Router::MATCH(Router::PUT)`
   *
   * Evaluates route criteria against PUT HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function PUT(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::PUT, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * @alias `Router::MATCH(Router::QUERY)`
   *
   * Evaluates route criteria against QUERY HTTP method, executing an optional callback upon match.
   *
   * @param string|array $cCriteria
   *   String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules.
   *     Router::FLAT and Router::IFLAT to compare direct string.
   *     Router::PREG and Router::IPREG to perform regular expressions. Avoid using the slashes.
   * @param callable|string|array|null $cCallback
   *   Optional callback to execute upon successful match.
   * @param bool $bTerminate
   *   Whether to terminate script execution after running the callback. Defaults to `true`.
   *
   * @return array|false Returns an array `[true, matches]` on success match, `[true, result]` on success callback, or `false` if not match.
   *
   * @throws InvalidArgumentException If `$cCriteria` contains no valid comparison string or fails to specify FLAT/PREG mode.
   */
  public static function QUERY(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false {
    return static::MATCH(static::QUERY, $cCriteria, $cCallback, $bTerminate);
  }

  /**
   * Maps a namespace and base path to a target class and method, forwarding the call.
   *
   * @param string $cNamespace The namespace to map.
   * @param string $cBasePath The base path.
   * @param int $bitMethods The bitmask specifying which methods to look for. Defaults to self::ANY.
   * @param bool $bTerminate Whether to terminate execution after forwarding the call. Defaults to true.
   * @return void
   * @throws RuntimeException If the target class does not exist, the method is not found, or if the method is not public.
   */
  public static function MapNamespace(
    string $cNamespace,
    string $cBasePath,
    int $bitMethods = self::ANY,
    bool $bTerminate = true
  ): void {
    if (static::$MethodBit === 0) static::init();
    if ((static::$MethodBit & $bitMethods) === 0) return;
    if (static::$PreventCallback) return;

    $cBasePath = '/' . trim($cBasePath, "/\n\r\t\v\0") . '/';
    $iBasePath = strlen($cBasePath);
    if (
      strlen(static::$Path) < $iBasePath ||
      substr(static::$Path, 0, $iBasePath) != $cBasePath
    ) return;

    $cLeadingPath = substr(static::$Path, $iBasePath);
    $cTargetClass = '\\' . preg_replace('/[^\da-z_]+/i', '\\', $cNamespace . '/' . $cLeadingPath);

    if (!class_exists($cTargetClass)) return;

    $rfReflectionClass = new ReflectionClass($cTargetClass);
    if (!$rfReflectionClass->hasMethod(static::$Method)) return;

    $rfReflectionMethod = $rfReflectionClass->getMethod(static::$Method);
    if (!$rfReflectionMethod->isPublic()) return;

    static::ForwardCallback([$cTargetClass, static::$Method]);
    if ($bTerminate) static::$PreventCallback = true;
  }
}
