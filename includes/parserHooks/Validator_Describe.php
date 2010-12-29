<?php

/**
 * Class for the 'describe' parser hooks.
 * 
 * @since 0.4.3
 * 
 * @file Validator_Describe.php
 * @ingroup Validator
 * 
 * @licence GNU GPL v3 or later
 *
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ValidatorDescribe extends ParserHook {
	
	/**
	 * No LST in pre-5.3 PHP *sigh*.
	 * This is to be refactored as soon as php >=5.3 becomes acceptable.
	 */
	public static function staticMagic( array &$magicWords, $langCode ) {
		$className = __CLASS__;
		$instance = new $className();
		return $instance->magic( $magicWords, $langCode );
	}
	
	/**
	 * No LST in pre-5.3 PHP *sigh*.
	 * This is to be refactored as soon as php >=5.3 becomes acceptable.
	 */	
	public static function staticInit( Parser &$wgParser ) {
		$className = __CLASS__;
		$instance = new $className();
		return $instance->init( $wgParser );
	}

	/**
	 * Gets the name of the parser hook.
	 * @see ParserHook::getName
	 * 
	 * @since 0.4.3
	 * 
	 * @return string
	 */
	protected function getName() {
		return 'describe';
	}	
	
	/**
	 * Returns an array containing the parameter info.
	 * @see ParserHook::getParameterInfo
	 * 
	 * @since 0.4.3
	 * 
	 * @return array of Parameter
	 */
	protected function getParameterInfo( $type ) {
		$params = array();

		$params['hooks'] = new ListParameter( 'hooks' );
		$params['hooks']->setDefault( array_keys( ParserHook::getRegisteredParserHooks() ) );
		
		$params['pre'] = new Parameter( 'pre', Parameter::TYPE_BOOLEAN );
		$params['pre']->setDefault( 'off' );
		
 		return $params;
	}	
	
	/**
	 * Returns the list of default parameters.
	 * @see ParserHook::getDefaultParameters
	 * 
	 * @since 0.4.3
	 * 
	 * @return array
	 */
	protected function getDefaultParameters( $type ) {
		return array( 'hooks' );
	}
	
	/**
	 * Renders and returns the output.
	 * @see ParserHook::render
	 * 
	 * @since 0.4.3
	 * 
	 * @param array $parameters
	 * 
	 * @return string
	 */
	public function render( array $parameters ) {
		$parts = array();
		
		foreach ( $parameters['hooks'] as $hookName ) {
			$parserHook = $this->getParserHookInstance( $hookName );
			
			if ( $parserHook === false ) {
				$parts[] = wfMsgExt( 'validator-describe-notfound', 'parsemag', $hookName );
			}
			else {
				$parts[] = $this->getParserHookDescription( $hookName, $parameters, $parserHook );
			}
		}
		
		$output = $this->parser->parse(
			implode( "\n\n", $parts ),
			$this->parser->mTitle,
			$this->parser->mOptions,
			true,
			false
		);
		
		return $output->getText();
	}
	
	protected function getParserHookDescription( $hookName, array $parameters, ParserHook $parserHook ) {
		$descriptionData = $parserHook->getDescriptionData( ParserHook::TYPE_TAG ); // TODO

		$description = "<h2> {$hookName} </h2>\n\n";
		
		$description .= $this->getParameterTable( $descriptionData['parameters'] );
		
		if ( $parameters['pre'] ) {
			$description = '<pre>' . $description . '</pre>';
		}
		
		return $description;
	}
	
	protected function getParameterTable( array $parameters ) {
		$tableRows = array();
		
		foreach ( $parameters as $parameter ) {
			$tableRows[] = $this->getDescriptionRow( $parameter );
		}
		
		if ( count( $tableRows ) > 0 ) {
			$tableRows = array_merge( array( '! Parameter
! Aliases
! Default
! Usage' ), $tableRows );
			
		$table = implode( "\n|-\n", $tableRows );
		
		$table = <<<EOT
{| class="wikitable sortable"
{$table}
|}
EOT;
		}
		
		return $table; // TODO
	}
	
	protected function getDescriptionRow( Parameter $parameter ) {
		$aliases = $parameter->getAliases();
		$aliases = count( $aliases ) > 0 ? implode( ', ', $aliases ) : '-';
		
		$default = $parameter->isRequired() ? "''required''" : $parameter->getDefault();
		if ( is_array( $default ) ) $default = implode( ', ', $default );  
		if ( $default === '' ) $default = "''empty''";
		
		// TODO
		
		return <<<EOT
| {$parameter->getName()}
| {$aliases}
| {$default}
| Description be here.
EOT;
	}
	
	/**
	 * Returns an instance of the class handling the specified parser hook,
	 * or false if there is none.
	 * 
	 * @since 0.4.3
	 * 
	 * @param string $parserHookName
	 * 
	 * @return mixed ParserHook or false
	 */
	protected function getParserHookInstance( $parserHookName ) {
		$className = ParserHook::getHookClassName( $parserHookName );
		return $className !== false && class_exists( $className ) ? new $className() : false;
	}
	
}