A PHP port of the HAML library.  

This code is the result of about six hours on an airplane and is not recommended for production use.  Please contribute patches for any errors you encounter.

Initialization
--------------
	require( "[path_to_library]/phamily.php" );
 	$parser = new PhamilyParser();

* Supports local variables being passed into the HAML renderer from the main code.

		$parser->render( "All you need is $object_of_craving", array( 'object_of_craving' => 'love' );
	    #=> All you need is love

* Uses a clean hash literal syntax for tag attributes

		$parser->render( "%h2{ 'class' => 'announce' } The dishes are done." );
		#=> <h2 class='announce'>The dishes are done.</h2>

* Recognizes indentation to open and close block constructs

 		$parser->render( "foreach( array( 1,2,3 ) as $number\n  = #$number ");
 		#=> #1 #2 #3
