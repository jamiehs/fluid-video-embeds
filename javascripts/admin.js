/**
 * Admin Control Panel JavaScripts
 * 
 * @version 1.0.0
 * @since 1.0.0
 */

(function($){
	FluidVideoEmbeds = function(){
		var self = this;
		var elems = {
			body: $('body')
		};
		
		this.helloWorld = function(){
			console.log("Hello World!");
		}
		
		// Init...
		this.helloWorld();
	};
	
    $(document).ready(function(){
    	if( $('#element .selector').length ){
	        window.FluidVideoEmbeds = new FluidVideoEmbeds();
    	}
    });
})(jQuery);