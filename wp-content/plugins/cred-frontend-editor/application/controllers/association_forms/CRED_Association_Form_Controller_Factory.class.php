<?php
class CRED_Association_Form_Controller_Factory{
	const CLASS_PREFIX = 'CRED_Association_Form_%s';

	public function __construct() {

	}

	public function build( $name, $factory = null, $helper = null ){
		$class_name = $this->build_class_name( $name );

		if( class_exists( $class_name ) ){
			return new $class_name( $factory, $helper );
		} else {
			throw new Exception( sprintf( 'Class with name %s does not exist!', $class_name ));
		}
	}

	/**
	 * @param $type
	 *
	 * @return string
	 */
	private function build_class_name( $name ){
		return sprintf(self::CLASS_PREFIX, $name );
	}
}