<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 7/11/17
 * Time: 4:44 PM
 */

namespace Scalar\Library\Mustache;
require_once "Cache.php";
require_once "Cache/AbstractCache.php";
require_once "Cache/NoopCache.php";
require_once "Cache/FilesystemCache.php";
require_once "Exception.php";
require_once "Exception/InvalidArgumentException.php";
require_once "Exception/LogicException.php";
require_once "Exception/RuntimeException.php";
require_once "Exception/SyntaxException.php";
require_once "Exception/UnknownFilterException.php";
require_once "Exception/UnknownHelperException.php";
require_once "Exception/UnknownTemplateException.php";
require_once "Logger.php";
require_once "Logger/AbstractLogger.php";
require_once "Logger/StreamLogger.php";
require_once "Loader.php";
require_once "Loader/MutableLoader.php";
require_once "Loader/ArrayLoader.php";
require_once "Loader/CascadingLoader.php";
require_once "Loader/FilesystemLoader.php";
require_once "Loader/InlineLoader.php";
require_once "Loader/ProductionFilesystemLoader.php";
require_once "Loader/StringLoader.php";
require_once "Source.php";
require_once "Source/FilesystemSource.php";
require_once "Compiler.php";
require_once "Context.php";
require_once "Engine.php";
require_once "HelperCollection.php";
require_once "LambdaHelper.php";
require_once "Parser.php";
require_once "Template.php";
require_once "Tokenizer.php";

class MustacheLoader
{


}
