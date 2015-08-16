window.addEvent('domready', function(){
	
				// Easy Edit™
				// Javier Onglao
				
	
				// Auto Complete for Tags
				var tags = new TextboxList('tags', {unique: true, plugins: {autocomplete: {placeholder: 'Add tags to this item.'}}});
				
				tags.container.addClass('textboxlist-loading');				
				new Request.JSON({url: 'includes/tags.php', onSuccess: function(r){
					tags.plugins['autocomplete'].setValues(r);
					tags.container.removeClass('textboxlist-loading');
				}}).send();	
				
				// Auto Complete for Places
				var places = new TextboxList('place', {max: 1, unique: true, plugins: {autocomplete: {placeholder: 'Choose the place of the purchase of this item.'}}});
				
				places.container.addClass('textboxlist-loading');				
				new Request.JSON({url: 'includes/places.php', onSuccess: function(r){
					places.plugins['autocomplete'].setValues(r);
					places.container.removeClass('textboxlist-loading');
				}}).send();	
				
				var detaileddailytotal = function(el) {
					
					$$('[rel=disable]').each(function(x,e) {
						if($(x).get('action') == "hide:detailed")
						{	$(x).morph('span.buttons');
							$(x).set('action', 'show:detailed');	}
						else
						{	$(x).morph('span.buttonsselected');
							$(x).set('action', 'hide:detailed');	}
					});
									 				
					$$('.detaileddaydisplay').each(function(e,s) {
						var r = $(e).getStyle('display');
						
						if(r == 'block')
						{	$(e).setStyle('display', 'none');	}
						else 
						{	$(e).setStyle('display', 'block');	}
					});
				};
				
				var purgeDetaileddailytotal = function(el) {
						
					$$('.detaileddaydisplay').each(function(e,s) {
						$(e).setStyle('display', 'none');
					});
				};
				
				//$('addForm').addEvent('submit', function() { alert(tags.getValues().clean()); });	
				$$('.buttons').each(function(slab, num)
				{
		
					$(slab).addEvent('mouseenter', function()
					{
						if(!this.get('selected'))
						{	this.setStyle('background', '#000066');
							this.setStyle('color', '#fff');
							this.setStyle('text-shadow', 'black 0.05em 0.05em 0.1em');	}
					});
					
					$(slab).addEvent('mouseleave', function()
					{
						if(!this.get('selected'))
						{	this.setStyle('background', '#99ccff');
							this.setStyle('color', '#000');
							this.setStyle('text-shadow', 'none');	}
					});
					
					$(slab).addEvent('click', function() {
						switch(this.get('action'))
						{
							case 'item:add':
								$('addBlock').setStyle('display', 'block');
								window.location = '#add';
								$('item').focus();
								
								$$('[action=item:add]').each(function(c,d) {
									$(c).set('text', 'Discard');
									$(c).set('action', 'item:discarde');
								});
							break;
							
							case 'item:discarde':
								$('addBlock').setStyle('display', 'none');
								window.location = '#top';
								
								$$('[action=item:discarde]').each(function(c,d) {
									$(c).set('text', 'Add');
									$(c).set('action', 'item:add');
								});
							break;
							
							case 'item:discard':
								$('addBlock').setStyle('display', 'none');
								window.location = '#top';
								
								$$('[action=item:discarde]').each(function(c,d) {
									$(c).set('text', 'Add');
									$(c).set('action', 'item:add');
								});
							break;
							
							case 'item:transfer':
								$('transferBlock').setStyle('display', 'block');
								window.location = '#add';
								
								$$('[action=item:transfer]').each(function(c,d) {
									$(c).set('text', 'Discard');
									$(c).set('action', 'item:transferdiscard');
								});
							break;
							
							case 'item:transferdiscard':
								$('transferBlock').setStyle('display', 'none');
								window.location = '#add';
								
								$$('[action=item:transferdiscard]').each(function(c,d) {
									$(c).set('text', 'Money');
									$(c).set('action', 'item:transfer');
								});
							break;
							
							case 'show:dailytotal':
								$$('.daydisplay').each(function(a,b) {
									$('day:'+b).set('html', $(a).get('html'));
									$(a).set('html', $('daytotal:'+b).get('value'));
									$(a).tween('background-color', '#FFCC33');
								});
								
								$$('[class=negprices]').each(function(x,e) {
									$(x).tween('background-color', '#FFff66');
								});
								
								$$('[class=posprices]').each(function(x,e) {
									$(x).tween('background-color', '#EEE');
								});
								
								$$('[rel=disable]').each(function(x,e) {
									 $(x).morph('span.buttons');
									 $(x).addClass('buttons');
									 $(x).setStyle('cursor', 'pointer');
									 $(x).addEvent('click', detaileddailytotal);
								});
								
								$$('[action=show:dailytotal]').each(function(c,d) {
									$(c).morph('span.buttonsselected');
									$(c).set('selected', true);
									$(c).set('action', 'hide:dailytotal');
								});
							break;
							
							case 'hide:dailytotal':
								$$('.daydisplay').each(function(a,b) {
									$(a).set('html', $('day:'+b).get('html'));
									$(a).tween('background-color', '#fff');
								});
								
								$$('[class=negprices]').each(function(x,e) {
									$(x).tween('background-color', '#fff');
								});
								
								$$('[class=posprices]').each(function(x,e) {
									$(x).tween('background-color', '#fff');
								});
								
								$$('[rel=disable]').each(function(x,e) {
									 $(x).morph('span.buttonsdis');
									 $(x).addClass('buttonsdis');
									 $(x).setStyle('cursor', 'default');
									 $(x).removeEvent('click', detaileddailytotal);
									 purgeDetaileddailytotal();
									 
									 $(x).set('action', 'show:detailed');
								});
								
								$$('[action=hide:dailytotal]').each(function(c,d) {
									$(c).morph('span.buttons');
									$(c).set('selected', false);
									$(c).set('action', 'show:dailytotal');
								});
							break;
							
							case 'toggle:individualprices':
								if($(this.get('name')).getStyle('display') == "block") {
									this.tween('width', '20px');
									$(this.get('name')).setStyle('display', 'none');
								}
								else {
									this.tween('width', '80px');
									$(this.get('name')).setStyle('display', 'block');
								}
							break;
							
							case 'hide:status':
								this.tween('display', 'none');
							break;
							
						}
					});
				});	
			});