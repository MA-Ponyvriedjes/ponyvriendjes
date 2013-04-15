site.com.inlinetoolbar = function(config){
	var toolbar;
	var isopen = false;
	
	config.container.on('click', '.toolbar .save',function(e){
			
		toolbar.jQuery().addClass('saving');
		toolbar.save();
		toolbar.close();
		e.preventDefault();

	}).on('click', '.toolbar .cancel',function(e){
		toolbar.jQuery().removeClass('editing');
		toolbar.cancel();
		toolbar.close();
		e.preventDefault();

	}).on('click', '.toolbar .edit a', function(e){
		
		if(config.type == 'redirect'){
			window.location = toolbar.getUrl();
		}else{
			if(toolbar.isOpen()){
				toolbar.close();
			}else{
				toolbar.open();
			}
		}
		e.preventDefault();
	}).on('focusin', '[data-name]',function(){
		toolbar.jQuery().find('[data-name]').removeClass('focus');
		$_(this).addClass('focus');
		toolbar.jQuery().addClass('editing');
	
	}).on('focusout', '[data-name]',function(){
		$_(this).removeClass('focus');
		if(toolbar.hasChanged() === false){
			toolbar.jQuery().removeClass('editing');
		}
	}).on('keypress', 'a[contenteditable="true"], span[contenteditable="true"], li[contenteditable="true"], h1[contenteditable="true"], h2[contenteditable="true"], h3[contenteditable="true"], h4[contenteditable="true"]',function(e){
		if(e.keyCode == 13){
			e.preventDefault();
		}
	});
	config.container.find('img[data-name]').each(function(){
		var uploader = site.com.inlineImageUploader({'jQuery':$_(this),'name':'image'});
		uploader.trigger("DisableBrowse", true);
		$_(this).data('uploader',uploader);
	});

	toolbar = (function(){
		var _olddata;

		function jQuery(){
			return config.container;
		}
		function getClasses(){
			return this.jQuery().attr('class').split(' ');
		}

		function getItems(id){

			if(this.jQuery().data('id')){
				return this.jQuery();
			}else if(id){
				return this.jQuery().find('[data-id="' + id + '"]');
			}else{
				return this.jQuery().find('[data-id]');
			}
		}
		function close(){
			this.isOpen(false);
			this.jQuery().removeClass('editmode').find('.toolbar').removeClass('active');
			this.setEditable(false);
			this.jQuery().find('[data-name]').removeClass('focus');
			return this;
		}

		function open(){
			_olddata = this.getData();
			this.jQuery().addClass('editmode').find('.toolbar').addClass('active');
			this.setEditable(true);
			this.jQuery().find('[data-name]').addClass('focus');
			this.isOpen(true);
			return this;
		}

		function isOpen(val){
			isopen = (val !== undefined)? val : isopen;
			return isopen;
		}

		function getData(){
			var data = {};
			var toolbar = this;
			//create local data object
			$_.each(this.getItems(),function(){
				var item = $_(this);
				var uid = ($_.isNumeric(item.data('id')))? 'id':'slug';
				data[item.data('id')] = {};
				
				
				//set url and token
				data[item.data('id')].url = 'index.php?option='+toolbar.getClasses()[0]+'&view='+toolbar.getClasses()[1] + '&' + uid + '='+item.data('id')+'&format=json';
				data[item.data('id')].data = {_token: $_('body').data('token')};
			
				//set each field
				$_.each(item.find('[data-name]'), function(){
					var el = $_(this);
					var fieldName = el.data('name');
					var itemId = item.data('id');
					var prop = el.prop("tagName");
					
					if(toolbar.isOpen()){
						if(prop === 'A' || prop === 'SPAN' || prop === 'LI' || prop === 'H1' || prop === 'H2' || prop === 'H3' || prop === 'H4'){
							data[itemId].data[fieldName] = $_(this).text();
						}else if(prop === 'DIV' && el.hasClass('redactor_text_only')){
							data[itemId].data[fieldName] = $_(this).getText();
						}else if(prop === 'DIV'){
							data[itemId].data[fieldName] = $_(this).getCode();
						}else if(prop === 'IMG'){
							data[itemId].data[fieldName] = $_(this).data('file');
						}
					}else{
						if(prop === 'IMG'){
							data[itemId].data[fieldName] = $_(this).data('file');
						}else{
							data[itemId].data[fieldName] = el.html();
						}
					}
					
				});
			});
			return data;
		}

		function setData(data){
			$_.each(this.getItems(),function(){
				var item = $_(this);
				
				$_.each(item.find('[data-name]'), function(){
					
					var prop = $_(this).prop("tagName");
					var name = $_(this).data('name');
					
					if(prop === 'A' || prop === 'SPAN' || prop === 'LI' || prop === 'H1' || prop === 'H2' || prop === 'H3' || prop === 'H4'){
						$_(this).text(data[item.data('id')].data[name]);
					}else if(prop === 'DIV'){
						$_(this).setCode(data[item.data('id')].data[name]);
					}else if(prop === 'IMG'){
						$_(this).data('file',data[item.data('id')].data[name]);
						$_(this).attr('src', $_(this).data('folder')+$_(this).data('file'));
					}
					
				});
			});
		}

		function hasChanged(id){
			var changed = false;
			
			$_.each(this.getItems(id),function(){
			
				var item = $_(this);
				
				$_.each(item.find('[data-name]'), function(){
					el = $_(this);
					if(_olddata[item.data('id')].data[el.data('name')] !== el.html()){
						changed = true;
					}
				});
			});
			return changed;
		}

		function save(){
			var toolbar = this;
			$_.each(this.getItems(),function(){
				
				var item = $_(this);
				var id = item.data('id');
				
				//save it to the server
				$_.post(toolbar.getData()[id].url,toolbar.getData()[id].data,function(result){
					toolbar.jQuery().removeClass('saving');
					console.log(result);
				});
				
			});

			return this;
		}

		function cancel(){
			
			this.setData(_olddata);
			
			return this;
		}

		function render(){
			//add toolbar
			return '<div class="toolbar"><ul class="list"><li class="edit"><a href="#"></a></li><li><a href="#" class="cancel">annuleren</a></li><li><a href="#" class="save">opslaan</a></li></ul></div>';
			
		}

		function setEditable(value){
			$_.each(this.getItems(),function(){
				var item = $_(this);
				var name;
				$_.each(item.find('[data-name]'), function(){
					
					var prop = $_(this).prop("tagName");
					
					if(value){
						if(prop === 'A' || prop === 'SPAN' || prop === 'LI' || prop === 'H1' || prop === 'H2' || prop === 'H3' || prop === 'H4'){
							$_(this).attr('contenteditable',value);
						}else if(prop === 'P'){
							name = $_(this).data('name');
							$_(this).removeAttr('data-name').wrap('<div class="redactor_text_only" data-name="' + name + '"/>').parent().redactor({toolbar:false});
						}else if(prop === 'DIV'){
							$_(this).redactor({toolbar:false, formattingTags: ['p']});
						}else if(prop === 'IMG'){
							$_(this).data('uploader').trigger("DisableBrowse", false);
						}
					}else{
						if(prop === 'A' || prop === 'SPAN' || prop === 'LI' || prop === 'H1' || prop === 'H2' || prop === 'H3' || prop === 'H4'){
							$_(this).attr('contenteditable',value);
						}else if(prop === 'DIV' && $_(this).hasClass('redactor_text_only')){
							name = $_(this).data('name');
							var text = $_(this).getText();
							$_(this).destroyEditor();
							$_(this).html('<p data-name="' + name + '">' + text + '</p>').find('p').unwrap();
						}else if(prop === 'DIV'){
							$_(this).destroyEditor();
						}else if(prop === 'IMG'){
							$_(this).data('uploader').trigger("DisableBrowse", true);
							//$_(this).removeClass('editable');
						}
					}
				});
			});
			return this;
		}

		function getUrl(item){
			item = (item)? item : this.jQuery();
		
			var classes = this.getClasses();
			var id = '';
			var category = '';
			if($_.isNumeric(item.data('id'))){
				id = '&id=' + item.data('id');
			}else if(item.data('id')){
				id = '&slug=' + item.data('id');
			}
			if(item.data('category')){
				category = '&category=' + item.data('category');
			}
			return 'index.php?option=' + classes[0] + '&view=' + classes[1] + id + category + '&layout=form';
			
		}
		return{

			jQuery:jQuery,
			getClasses:getClasses,
			getItems:getItems,
			
			open:open,
			close:close,
			isOpen:isOpen,
			getData:getData,
			setData:setData,
			hasChanged:hasChanged,
			save:save,
			cancel:cancel,
			render:render,
			setEditable:setEditable,
			getUrl:getUrl

		};}());
	return toolbar;
};