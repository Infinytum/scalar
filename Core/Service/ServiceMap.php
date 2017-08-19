<?php

namespace Scalar\Core\Service;


use Scalar\Util\ScalarArray;

/**
 * The ServiceMap class provides an easy way for foreign classes
 * to register services which will then be exposed in the
 * service map that will be available to other classes.
 *
 * Class ServiceMap
 * @package Scalar\Core\Service
 */
class ServiceMap
{

    /**
     * Constants for exception handling
     */
    const EXCEPTION_INVALID_SERVICE = 'An invalid object or value was passed to the "service" parameter of the ServiceMap constructor!';
    const EXCEPTION_NO_SUCH_CLASS = 'An non-existent class for service creation was passed to the ServiceMap! Faulty class: ';
    const EXCEPTION_SERVICE_ALREADY_REGISTERED = 'Tried to register a service that was already registered! Faulty service: ';

    /**
     * Constants for service managing
     */
    const SERVICE_TYPE_VALUE = 'value';
    const SERVICE_TYPE_CLASS = 'class';
    const SERVICE_TYPE_SINGLETON = 'singleton';

    const SERVICE_DESCRIPTOR_TYPE = 'type';
    const SERVICE_DESCRIPTOR_VALUE = 'value';
    const SERVICE_DESCRIPTOR_INSTANCE = 'instance';
    const SERVICE_DESCRIPTOR_ARGUMENTS = 'arguments';

    /**
     * This value contains the configuration for different services
     * that will be added by foreign classes top provide easy
     * access to these services.
     *
     * @var ScalarArray
     */
    private $services;

    /**
     * Create a new instance of a service map to store services in
     *
     * @param array|ScalarArray $services This can be a pre-initialized array/ScalarArray
     * which already contains some service definitions
     * @throws \InvalidArgumentException This exception will be thrown when a different object/value
     * is passed to the constructor than expected
     */
    public function __construct
    (
        $services = []
    )
    {
        if (is_array($services) && !$services instanceof ScalarArray) {
            $services = new ScalarArray($services);
        } else if ($services = null) {
            $services = new ScalarArray();
        } else {
            throw new \InvalidArgumentException
            (
                self::EXCEPTION_INVALID_SERVICE
            );
        }

        $this->services = $services;
    }

    /**
     * Retrieve an instance from the service you specify in the $serviceName parameter
     *
     * @param string $serviceName Name of the service you want to get an instance of
     * @return mixed|null|object Will return an instance of your Service or the stored value. Null if nothing was found
     */
    public function getService
    (
        $serviceName
    )
    {
        $returnValue = null;

        if ($this->services->contains($serviceName)) {
            $serviceDescriptor = new ScalarArray($this->services->get($serviceName));

            try {
                switch ($serviceDescriptor->get(self::SERVICE_DESCRIPTOR_TYPE)) {
                    case self::SERVICE_TYPE_VALUE:
                        $returnValue = $serviceDescriptor->get(self::SERVICE_DESCRIPTOR_VALUE);
                        break;

                    case self::SERVICE_TYPE_CLASS:
                        $returnValue = $this->createServiceInstance
                        (
                            $serviceDescriptor->get(self::SERVICE_DESCRIPTOR_VALUE),
                            $serviceDescriptor->get(self::SERVICE_DESCRIPTOR_ARGUMENTS)
                        );
                        break;

                    case self::SERVICE_TYPE_SINGLETON:
                        if ($serviceDescriptor->get(self::SERVICE_DESCRIPTOR_INSTANCE) === null) {
                            $returnValue = $this->createServiceInstance
                            (
                                $serviceDescriptor->get(self::SERVICE_DESCRIPTOR_VALUE),
                                $serviceDescriptor->get(self::SERVICE_DESCRIPTOR_ARGUMENTS)
                            );
                            $serviceDescriptor->set(self::SERVICE_DESCRIPTOR_INSTANCE, $returnValue);
                        } else {
                            $returnValue = $serviceDescriptor->get(self::SERVICE_DESCRIPTOR_INSTANCE);
                        }
                        break;
                }
            } catch (\Exception $exception) {
                echo $exception->getMessage();
                // TODO: Send error to core logger
            }

            $this->services->set($serviceName, $serviceDescriptor->asArray());
        }

        return $returnValue;
    }

    /**
     * @param string $class The name of the class you want to create a new instance of
     * @param array $arguments This array will be used for the constructor arguments
     * @return object Will return a new instance of the passed class with the specified arguments
     * @throws \Exception Will be thrown when passing a non-existent class to this method
     */
    private function createServiceInstance
    (
        $class,
        $arguments
    )
    {
        if (!class_exists($class)) {
            throw new \Exception
            (
                self::EXCEPTION_NO_SUCH_CLASS . $class
            );
        }

        $serviceInstance = null;

        if ($arguments === null || count($arguments) === 0) {
            $serviceInstance = new $class;
        } else {
            $reflectionClass = new \ReflectionClass($class);

            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $serviceInstance = $reflectionClass->newInstanceArgs($arguments);
        }

        return $serviceInstance;
    }

    /**
     * Register a service class in this service map
     *
     * @param string $serviceName Name of the service you want to register
     * @param string $class Name of the class (with full namespace) which should be exposed
     * @param array $arguments Arguments which should be passed to the class constructor
     * @param bool $singleton Should there only be a single instance of this class
     * @return static $this
     * @throws \Exception This is thrown when a service is already registered
     */
    public function registerServiceClass
    (
        $serviceName,
        $class,
        $arguments = [],
        $singleton = false
    )
    {
        if ($this->hasService($serviceName)) {
            throw new \Exception
            (
                self::EXCEPTION_SERVICE_ALREADY_REGISTERED . $serviceName
            );
        }

        $serviceType = $singleton ? self::SERVICE_TYPE_SINGLETON : self::SERVICE_TYPE_CLASS;

        $this->services->set
        (
            $serviceName,
            [
                self::SERVICE_DESCRIPTOR_TYPE => $serviceType,
                self::SERVICE_DESCRIPTOR_VALUE => $class,
                self::SERVICE_DESCRIPTOR_ARGUMENTS => $arguments,
                self::SERVICE_DESCRIPTOR_INSTANCE => null
            ]
        );

        return $this;
    }

    /**
     * Check if a service is present in this service map
     *
     * @param string $serviceName Name of the service you want to check
     * @return bool
     */
    public function hasService
    (
        $serviceName
    )
    {
        return $this->services->contains($serviceName);
    }

    /**
     * Register a service value in this service map
     *
     * @param string $serviceName Name of the service you want to register
     * @param mixed $value Value which should be returned when you request the service from the service map
     * @return $this
     * @throws \Exception This is thrown when a service is already registered
     */
    public function registerServiceValue
    (
        $serviceName,
        $value
    )
    {
        if ($this->hasService($serviceName)) {
            throw new \Exception
            (
                self::EXCEPTION_SERVICE_ALREADY_REGISTERED . $serviceName
            );
        }

        $this->services->set
        (
            $serviceName,
            [
                self::SERVICE_DESCRIPTOR_TYPE => self::SERVICE_TYPE_VALUE,
                self::SERVICE_DESCRIPTOR_VALUE => $value
            ]
        );

        return $this;
    }

    /**
     * Unregister a service from the service map
     *
     * @param string $serviceName Name of the service you want to unregister
     * @return $this
     */
    public function unregisterService
    (
        $serviceName
    )
    {
        if ($this->hasService($serviceName)) {
            $this->services->delete($serviceName);
        }

        return $this;
    }

}