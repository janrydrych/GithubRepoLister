<?php
/**
 * This file is part of the GithubRepoLister project
 */
namespace Tests\GRL;

require_once __DIR__ . '/../../vendor/autoload.php';

use GRL\Configuration\Configuration;
use GRL\Configuration\Services;
use GRL\DIC;
use GRL\Util\FlashMessages;
use PHPUnit\Framework\TestCase;

/**
 * Tests for DIC class
 *
 * @author Jan Rydrych <jan.rydrych@gmail.com>
 */
class DICTest extends TestCase
{
	/**
	 * @return DIC
	 */
	protected function createDIC(): DIC
	{
		$configMock = $this->getMockBuilder(Configuration::class)
		                   ->getMock();

		$servicesMock = $this->getMockBuilder(Services::class)
		                     ->getMock();

		return new DIC($configMock, $servicesMock);
	}

	/**
	 * @return Configuration
	 */
	private function mockConfiguration(): Configuration
	{
		$configurationDummy = new class extends Configuration {
			public function toDIC(DIC $dic)
			{
				$dic->set('k1', 'value');
			}
		};
		return $configurationDummy;
	}

	/**
	 * @return Services
	 */
	private function mockServices(): Services
	{
		$mockServices = new class extends Services {
			public $mockService;
			public function toDIC(DIC $dic)
			{
				$dic->addService('s1', $this->mockService);
			}
		};
		$mockServices->mockService = new \stdClass();
		return $mockServices;
	}

	public function testConstructorDoesNotCallInitializeWithNullArguments()
	{
		$dic = new DIC();

		$this->assertFalse($dic->isInitialized(),
		                  '->isInitialized() must not be called when valid Configuration argument is not passed');
		$this->assertNull($dic->get('k1'),
		                  '->initializeDIC() must not be called when valid Configuration argument is not passed');
	}

	public function testConstructorCallsInitializeWithValidArguments()
	{
		$dic = new DIC($this->mockConfiguration(), $this->mockServices());

		$this->assertTrue($dic->isInitialized(),
		                  '->isInitialized() must be called when valid Configuration argument is not passed');
		$this->assertSame('value',
		                  $dic->get('k1'),
		                  '->initializeDIC() must be called when valid Configuration argument is not passed');
	}

	public function testInitializeDICDoesNotSetNeitherParametersNorServicesWithNullArguments()
	{
		$dic = new DIC();
		$dic->initializeDIC();

		$this->assertNull($dic->get('k1'),
		                  '->initializeDIC() must not initialize any parameters when Configuration argument is not passed');
		try {
			$dic->getService('s1');
			$this->fail('->initializeDIC() must not initialize any services when Services argument is not passed');
		} catch (\Exception $e) {
			$this->assertInstanceOf(\InvalidArgumentException::class,
			                        $e,
			                        '->initializeDIC() must not initialize any services when Services argument is not passed');
		}
	}

	public function testInitializeDICSetsParametersAndServicesWithValidArguments()
	{
		$dic = new DIC();
		$mockServices = $this->mockServices();
		$dic->initializeDIC($this->mockConfiguration(), $mockServices);

		$this->assertSame('value',
		                  $dic->get('k1'),
		                  '->initializeDIC() must initialize parameters when Configuration argument is passed');
		$this->assertSame($mockServices->mockService,
		                  $dic->getService('s1'),
		                  '->initializeDIC() must initialize services when both Configuration and Services arguments are passed');
	}

	public function testInitializeDICDosNotInitializeWhenDICIsAlreadyInitialized()
	{
		$dic = new DIC();
		$dic->initializeDIC($this->mockConfiguration());

		$mockConfiguration2 = new class extends Configuration
		{
			public function toDIC(DIC $dic)
			{
				$dic->set('k1', 'newValue');
			}
		};
		$dic->initializeDIC($mockConfiguration2);
		$this->assertSame('value',
		                  $dic->get('k1'),
		                  '->initializeDIC() must not initialize DIC when it is initialized already');
	}

	public function testIsInitialized()
	{
		$dic = new DIC();
		$this->assertFalse($dic->isInitialized(), '->isInitialized() must be false by default');
		$dic->initializeDIC($this->mockConfiguration(), $this->mockServices());
		$this->assertTrue($dic->isInitialized(), '->isInitialized() must be true after initialization');
	}

	public function testPropertyGetSet()
	{
		$DIC = $this->createDIC();
		$DIC->set('k1', 'testValue');
		$this->assertSame('testValue', $DIC->get('k1'), '->set() must set the string value of a new parameter');

		$DIC->set('k1', 'newValue');
		$this->assertSame('newValue', $DIC->get('k1'), '->set() must override previously set parameter');

		$DIC->set('k2', 15);
		$this->assertSame(15, $DIC->get('k2'), '->set() must set the int value of a new parameter');

		$DIC->set('k3', 0.1);
		$this->assertSame(0.1, $DIC->get('k3'), '->set() must set the float value of a new parameter');

		$DIC->set('k4', array('foo' => 'bar'));
		$this->assertSame(array('foo' => 'bar'),
		                  $DIC->get('k4'),
		                  '->set() must set the array value of a new parameter');

		$object = new \stdClass();
		$DIC->set('k5', $object);
		$this->assertSame($object, $DIC->get('k5'), '->set() must set the object value of a new parameter');

		$this->assertNull($DIC->get('k6'), '->get() must return null for nonexistent parameter');
	}

	public function testAddHasGetService()
	{
		$DIC = $this->createDIC();
		$service = new \stdClass();
		$this->assertFalse($DIC->hasService('s1'), '->hasService() must return false when service is not set');
		$DIC->addService('s1', $service);
		$this->assertTrue($DIC->hasService('s1'), '->hasService() must return true when service is set');
		$this->assertSame($service, $DIC->getService('s1'), '->addService() must set a new service');
	}

	public function testRemoveService()
	{
		$DIC = $this->createDIC();
		$service = new \stdClass();
		$DIC->addService('s1', $service);

		$DIC->removeService('s1');
		try {
			$DIC->getService('s1');
			$this->fail('->removeService() must unset a service');
		} catch (\Exception $e) {
			$this->assertInstanceOf(\InvalidArgumentException::class, $e, '->removeService() must unset a service');
		}
	}

	public function testAddServiceThrowsExceptionWhenServiceAlreadyExists()
	{
		$DIC = $this->createDIC();
		try {
			$DIC->addService('s1', new \stdClass());
			$DIC->addService('s1', new \stdClass());
			$this->fail('->addService() must throw an \InvalidArgumentException if the service already exist');
		} catch (\Exception $e) {
			$this->assertInstanceOf(\InvalidArgumentException::class,
			                        $e,
			                        '->addService() must throw an \InvalidArgumentException if service already exists');
			$this->assertEquals('Service s1 already exists.',
			                    $e->getMessage(),
			                    '->addService() must throw an \InvalidArgumentException if service already exists');
		}
	}

	public function testAddServiceThrowsExceptionWhenServiceArgumentIsNotObject()
	{
		$DIC = $this->createDIC();
		try {
			$DIC->addService('s2', 'string');
			$this->fail('->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
		} catch (\Exception $e) {
			$this->assertInstanceOf(\InvalidArgumentException::class,
			                        $e,
			                        '->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
			$this->assertEquals('Service s2 must be an object, string given.',
			                    $e->getMessage(),
			                    '->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
		}

		try {
			$DIC->addService('s3', 1);
			$this->fail('->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
		} catch (\Exception $e) {
			$this->assertInstanceOf(\InvalidArgumentException::class,
			                        $e,
			                        '->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
			$this->assertEquals('Service s3 must be an object, integer given.',
			                    $e->getMessage(),
			                    '->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
		}

		try {
			$DIC->addService('s4', array());
			$this->fail('->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
		} catch (\Exception $e) {
			$this->assertInstanceOf(\InvalidArgumentException::class,
			                        $e,
			                        '->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
			$this->assertEquals('Service s4 must be an object, array given.',
			                    $e->getMessage(),
			                    '->addService() must throw an \InvalidArgumentException if the service parameter is not an object');
		}
	}

	public function testGetServiceThrowsExceptionWhenServiceIsNotSet()
	{
		$DIC = $this->createDIC();
		try {
			$DIC->getService('s1');
			$this->fail('->getService() must throw an \InvalidArgumentException if the service does not exist');
		} catch (\Exception $e) {
			$this->assertInstanceOf(\InvalidArgumentException::class,
			                        $e,
			                        '->getService() must throw an \InvalidArgumentException if the service does not exist');
			$this->assertEquals('Service s1 does not exist.',
			                    $e->getMessage(),
			                    '->getService() must throw an \InvalidArgumentException if the service does not exist');
		}
	}

	public function testGetFlashBag()
	{
		$DIC = $this->createDIC();
		$this->assertNull($DIC->getFlashBag(), '->getFlashBag() must return null when service is not set');

		$flashBag = new FlashMessages();
		$DIC->addService('flashBag', $flashBag);
		$this->assertInstanceOf(FlashMessages::class,
		                        $DIC->getFlashBag(),
		                        '->getFlashBag() must return instance of FlashMessages class');
	}
}
