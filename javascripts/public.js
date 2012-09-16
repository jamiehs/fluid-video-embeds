(function($){
	FluidVideoEmbeds = function(){
		var self = this;
		var elems = {
			imageEmbeds: $('.fve-video-wrapper.fve-image-embed')
		};
		
		this.bindClickEvents = function(){
			elems.imageEmbeds.bind('click', function(event){
				var wrapper = $(this);
				var image = $(this).find('img');
				var fullImage = wrapper.attr('data-full-image');
				var iframeURL = wrapper.attr('data-iframe-url');
				
				// Replace the image with an iFrame
				image.replaceWith( window.FluidVideoEmedsiFrameBeforeSrc + iframeURL + window.FluidVideoEmedsiFrameAfterSrc );
			});
		}
		
		this.init = function(){
			this.bindClickEvents();
		}
		
		// Init...
		this.init();
	};
	
    $(document).ready(function(){
    	if( $('.fve-video-wrapper.fve-image-embed').length ){
	        window.FluidVideoEmbeds = new FluidVideoEmbeds();
    	}
    });
})(jQuery);