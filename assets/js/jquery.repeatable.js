/**************************************************************************************************************
  
      NAME
          thrak.ui.repeatable-1.0.0.js
  
      DESCRIPTION
          Implements a repeatable form field.
  
      AUTHOR
          Christian Vigh, 10/2015.
  
      HISTORY
      [Version : 1.0]    [Date : 2015/10/11]     [Author : CV]
          Initial version.
  
   **************************************************************************************************************/

 ( function ( $ )
   {
	$. widget 
	   (
		'thrak.repeatable',
		{
			// Widget options
			options		:  
			   {	
				// Minimum and maximum number of template instances
				minInstances		:  1,
				maxInstances		:  4
			    },
			// Data internal to each widget instance
			data		:
			   {
				currentId			:  0,		// Id for the tags having an "id=" attribute within the template
				template			:  null,	// Template data
				buttonsetTemplate		:  null,	// Buttonset template
				repeatableButtonset		:  null,	// True if the buttonset template is located inside a template
				defaultButtonsetTemplate	:  '<div class="repeatable-buttonset repeatable-buttonset-default" target="#target">' +
								   '	<div class="repeatable-button repeatable-button-minus" title="#delete"></div>' +
								   '	<div class="repeatable-button repeatable-button-plus" title="#add"></div>' +
								   '</div>',
				defaultButtonsetPosition	:  'last'	// Button set position in the template
			    },
			// Message strings - common to all instances
			messages	:
			   {
				'fr'	:
				   {
					addButtonTooltip	:  "Ajoute un &eacute;l&eacute;ment",
					deleteButtonTooltip	:  "Supprime cet &eacute;l&eacute;ment"
				    },
				'en'	:
				   {
					addButtonTooltip	:  "Add an item",
					deleteButtonTooltip	:  "Remove this item"
				    }
			    },

			// _create -
			//	Creates a new instance of the widget. At least minInstances instances of the template will be created.
			_create			:  function ( )
			   {
				var	$this		=  this. element ;
				var	$parent		=  $this. parent ( ) ;
				var	this_id		=  $this. attr ( 'id' ) ;

				// Internal data
				this. options. data	=  $. extend ( {}, this. data ) ;

				// Get min and max instances
				var	min_instances	=  parseInt ( $this. attr ( 'min-instances' ) ) ;
					max_instances	=  parseInt ( $this. attr ( 'max-instances' ) ) ;

				if  ( ! isNaN ( min_instances ) )
					this. options. minInstances	=  min_instances ;

				if  ( ! isNaN ( max_instances ) )
					this. options. maxInstances	=  max_instances ;

				// One instance is the minimum ; it's up to you to hide it if not needed
				if  ( this. options. minInstances  <  1 )
					this. options. minInstances	=  1 ;

				// Make sure that min instances is not greater than max instances
				// Silently adjust max instances if needed
				if  ( this. options. maxInstances  <  this. options. minInstances )
					this. options. maxInstances	=  this. options. maxInstances ;

				// Remove the min-instances and max-instances attributes from the template
				$this. removeAttr ( 'min-instances' ). removeAttr ( 'max-instances' );

				// Get buttonset 
				var	$buttonset		=  $('.repeatable-buttonset[target="' + this_id + '"]') ;
				var	$buttonset_parent	=  $buttonset. parent ( '.repeatable' ) ;
				var	is_inside		=  1 ;

				if  ( $buttonset. length  ==  0 )
				   {
					var	lang		=  ( $. locale ) ?  $. locale ( ) : 'en' ;
					var	template	=  this. options. data. defaultButtonsetTemplate
									.replace ( /#target/, this_id )
									.replace ( /#add/   , this. messages [ lang ]. addButtonTooltip )
									.replace ( /#delete/, this. messages [ lang ]. deleteButtonTooltip ) ;

					if  ( this. options. data. defaultButtonsetPosition  ==  'last' )
					   {
						$this. append ( template ) ;
						$('.repeatable-buttonset', $this). addClass ( 'repeatable-buttonset-last' ) ;
					    }
					else
					   {
						$this. prepend ( template ) ;
						$('.repeatable-buttonset', $this). addClass ( 'repeatable-buttonset-first' ) ;
					    }

					this. options. data. buttonsetTemplate	=  template ;
				    }
				else 
				   {
					if  ( $buttonset_parent. length  ==  0 )
						is_inside	=  0 ;

					// Save the repeatable buttonset template and a flag indicating whether it's inside or outside
					// the repetable construct
					this. options. data. buttonsetTemplate		=  $buttonset. outerHtml ( ) ;
					this. options. data. repeatableButtonset	=  is_inside ;
				    }

				// Save the repeatable template
				this. options. data. template		=  $this [0]. outerHTML ;
				
				// Remove the template from the DOM
				$this. html ( '' ) ;

				// Create as least minInstances instances
				this. _create_initial_instances ( ) ;
			    },

			// _create_initial_instances -
			//	Ensures that at least minInstances instances of the template are present.
			//	This function is also called when everything is re-initialized.
			_create_initial_instances	:  function ( ) 
			   {
				var	$this		=  this. element ;
				var	instances	=  $('.repeatable-instance', $this. parent ( )) ;
				var	length		=  instances. length ;

				this. options. data. currentId	=  length ;

				for  ( var  i  =  length ; i  <  this. options. minInstances ; i ++ )
					this. _append_from_template ( ) ;

				this. _update_buttonset ( ) ;
			    },

			// _append_from_template -
			//	Appends an instance of the template at the end of the widget.
			_append_from_template		:  function ( )
			   {
				var	$widget		=  this ;
				var	$this		=  this. element ;
				var	$parent		=  $this. parent ( ) ;
				var	instances	=  $('.repeatable-instance', $this. parent ( )) ;
				var	length		=  instances. length ;


				if  ( length  ==  this. options. maxInstances )
					return ;

				var	instance	=  $parent. append ( this. options. data. template ). children ( ). last ( ) ;

				// Increment current id, which will be used to renumber all the tags having an "id" attribute
				this. options. data. currentId ++ ;

				// Identify this new element as a template instance
				instance. addClass ( 'repeatable-instance' ) ;

				// Add event handlers
				$('.repeatable-button-plus', instance  ). click 
				   ( 
					function ( e ) 
					   { 
						var	$this		=  $(this) ;

						if  ( $this. hasClass ( 'repeatable-button-disabled' ) )
						   {
							e  &&  e. preventDefault  &&  e. preventDefault ( ) ;
							e  &&  e. stopPropagation  &&  e. stopPropagation ( ) ;
							e  &&  e. stopImmediatePropagation  &&  e. stopImmediatePropagation ( ) ;

							return ( false ) ;
						    }

						$widget. _add_instance. apply ( $widget ) ; 
					    } 
				    ) ;

				$('.repeatable-button-minus', instance ). click 
				   ( 
					function ( e ) 
					   { 
						var	$this		=  $(this) ;

						if  ( $this. hasClass ( 'repeatable-button-disabled' ) )
						   {
							e  &&  e. preventDefault  &&  e. preventDefault ( ) ;
							e  &&  e. stopPropagation  &&  e. stopPropagation ( ) ;
							e  &&  e. stopImmediatePropagation  &&  e. stopImmediatePropagation ( ) ;

							return ( false ) ;
						    }

						$widget. _delete_instance. apply ( $widget, [ instance ] ) 
					    } 
				    ) ;

				// Renumber every element having an "id" attribute within the template
				$('[id]:not([data-index])', instance). each
				   (
					function  ( index, item )
					   {
						var	$this	=  $(item) ;
						var	id	=  $this. attr ( 'id' ) ;
						var	name	=  $this. attr ( 'name' ) ;
						var	newid	=  id + "_" + $widget. options. data. currentId ;

						$this. attr ( 'id', newid ) ;
						$this. attr ( 'data-index', $widget. options. data. currentId ) ;

						// "name" attribute is changed in two cases :
						// - It is undefined
						// - It has the same value as the "id" attribute
						if  ( name  ===  undefined  ||  name  ==  id )
							$this. attr ( 'name', newid ) ;
					    }
				    ) ;
			    },

			// _update_buttonsset -
			//	Updates the enabled/disabled state of plus/minus buttons and others
			_update_buttonset	:  function ( )
			   {
				var	instances	=  $('.repeatable-instance', this. element. parent ( ) ) ;
				var	min		=  this. options. minInstances,
					max		=  this. options. maxInstances ;

				if  ( min  == max )
				   {
					$('.repeatable-button-minus, .repeateable-button-minus'). addClass ( 'repeatable-button-none' ) ;

					return ;
				    }

				var	length		=  instances. length ;

				if  ( length  ==  min )
				   {
					$('.repeatable-button-minus', instances). addClass    ( 'repeatable-button-disabled' ) ;
					$('.repeatable-button-plus' , instances). removeClass ( 'repeatable-button-disabled' ) ;
				    }
				else if  ( length  ==  max )
				   {
					$('.repeatable-button-minus', instances). removeClass ( 'repeatable-button-disabled' ) ;
					$('.repeatable-button-plus' , instances). addClass    ( 'repeatable-button-disabled' ) ;
				    }
				else
				   {
					$('.repeatable-button-minus', instances). removeClass ( 'repeatable-button-disabled' ) ;
					$('.repeatable-button-plus' , instances). removeClass ( 'repeatable-button-disabled' ) ;
				    }
			    },

			// _add_instance -
			//	Called when the "add instance" link is clicked
			_add_instance		:  function ( )
			   {
				this. _append_from_template ( ) ;
				this. _update_buttonset ( ) ;
			    },

			// _delete_instance -
			//	Called when the "remove instance" link is clicked
			_delete_instance	:  function ( instance )
			   {
				instance. remove ( ) ;
				this. _create_initial_instances ( ) ;
			    },

			// _delete_all_instances -
			//	Called when the "delete all instances" link is cliked
			_delete_all_instances	:  function ( $widget )
			   {
				$('.repeatable-instance', $widget. element). remove ( ) ;
				$widget. _create_initial_instances ( ) ;
			    },
		 }
	    ) ;


    } ( jQuery ) ) ;

