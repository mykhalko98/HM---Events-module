<div class="modal fade dont-use-grid" id="modal-events_admin_settings" tabindex="-1" role="dialog" aria-labelledby="modal-events_admin_settings-label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-tabs pb-0">
                <h5 class="modal-title" id="modal-page-setting-label">
                    <div class="nav nav-tabs nav-tabs-pb" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-categories-tab" data-toggle="tab" href="#nav-categories" role="tab" aria-controls="nav-categories"
                           aria-selected="true">{{ __('events::lang_admin.CATEGORIES_TAB') }}</a>
                        <a class="nav-item nav-link" id="nav-tags-tab" data-toggle="tab" href="#nav-tags" role="tab" aria-controls="nav-tags"
                           aria-selected="false">{{ __('events::lang_admin.TAGS_TAB') }}</a>
                    </div>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="{{ __('events::lang_admin.CLOSE_BUTTON') }}">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <div class="tab-content" id="nav-tab-content">
                    <div class="tab-pane fade show active" id="nav-categories" role="tabpanel" aria-labelledby="nav-categories-tab">
                        <script>
							function placeCaretAtEnd(el) {
								el.focus();
								if (typeof window.getSelection != "undefined"
									&& typeof document.createRange != "undefined") {
									var range = document.createRange();
									range.selectNodeContents(el);
									range.collapse(false);
									var sel = window.getSelection();
									sel.removeAllRanges();
									sel.addRange(range);
								} else if (typeof document.body.createTextRange != "undefined") {
									var textRange = document.body.createTextRange();
									textRange.moveToElementText(el);
									textRange.collapse(false);
									textRange.select();
								}
							}

                            $(document).on('blur keyup paste input', 'td[data-field-name="name"] span span[contenteditable="true"]', function(event) {
                            	let $category = $(event.target);
								$category.data('origin_value') === undefined && $category.data('origin_value', $category.text().trim());
								clearTimeout($category.data('timeout_id'));
								$category.data('origin_value') !== $category.text().trim() && $category.data('timeout_id', setTimeout((function($category){return function(){categoryClass.saveEdited($category);};})($category), 700));
                            });

							$(document).on('blur keyup paste input', 'td[data-field-name="tag"] span span[contenteditable="true"]', function(event) {
								let $tag = $(event.target);
								$tag.data('origin_value') === undefined && $tag.data('origin_value', $tag.text().trim());
								clearTimeout($tag.data('timeout_id'));
								$tag.data('origin_value') !== $tag.text().trim() && $tag.data('timeout_id', setTimeout((function($category){return function(){tagClass.saveEdited($category);};})($tag), 700));
							});
						</script>
                        <script>
							var categoryClass = {
								edit: function(event) {
									let $btn           = $(event.target);
									let $category_cell = $btn.closest('td').siblings('td[data-field-name="name"]');
									$category_cell     = $category_cell.find('span span');

									$category_cell.prop('contenteditable', true);
									placeCaretAtEnd($category_cell.get(0));
                                },
								saveEdited: function($category) {
									let url  = $category.attr('data-url');
                                    let data = {
                                        '_token': $('meta[name=csrf-token]').attr('content'),
                                        '_method': 'POST',
                                        'name': $category.text().trim()
                                    };
									$.ajax({
										url: url,
										type: 'POST',
										dataType: "JSON",
										data: data,
										success: function (response) {
											$category.data('origin_value', $category.text().trim());
											if(response.status) {
												$.toast({
													heading: 'Information',
													text: response.message ? response.message : 'Successfully edited.',
													showHideTransition: 'slide',
													icon: 'info'
												});
											} else {
												$.toast({
													heading: 'Error',
													text: response.errors ? response.errors : 'Error',
													showHideTransition: 'slide',
													icon: 'error'
												});
											}
										},
										error: function(error) {
											$category.data('origin_value', $category.text().trim());
											$.toast({
												heading: 'Error',
												text: error.errors ? error.errors : 'Error',
												showHideTransition: 'slide',
												icon: 'error'
											});
										}
									});
                                },
                                create: function(event) {
									let $form = $(event.target);
									let $btn  = $form.find('button[type="submit"]');
									let url   = $form.attr('action');
									let data  = {
										'_token': $('meta[name=csrf-token]').attr('content'),
										'_method': $form.attr('method'),
										'name': $form.find('input[name="category_name"]').val()
									};

									$btn.addClass('loading');
									$.ajax({
										url: url,
										type: 'POST',
										dataType: "JSON",
										data: data,
										success: function (response) {
											if(response.status) {
												$.toast({
													heading: 'Information',
													text: response.message ? response.message : 'Successfully created.',
													showHideTransition: 'slide',
													icon: 'info'
												});
												if(response.category) {
													if($form.closest('.tab-pane').find('#categories_table tbody tr:last-child').length) {
														let newRow = $form.closest('.tab-pane').find('#categories_table tbody tr:last-child').clone();
														$form.closest('.tab-pane').find('#categories_table tbody tr:last-child').after(newRow);													
														newRow.attr('data-row-id', response.category.id);
														newRow.find('[data-field-name="id"] span span').html(response.category.id);
														newRow.find('[data-field-name="name"] span span').html(response.category.name);
														newRow.find('[data-field-name="name"] [data-url$="/edit"]').length ? newRow.find('[data-field-name="name"] [data-url$="/edit"]').attr('data-url', newRow.find('[data-field-name="name"] [data-url$="/edit"]').attr('data-url').replace(/\/[0-9]+\/edit/, '/'+response.category.id+'/edit')) : '';
														newRow.find('[data-field-name="events_number"] span span').html(response.category.events_number);
														newRow.find('[data-field-name="date"] span span').html(response.category.date);
														newRow.find('[data-field-name="actions"] [data-url$="/delete"]').length ? newRow.find('[data-field-name="actions"] [data-url$="/delete"]').attr('data-url', newRow.find('[data-field-name="actions"] [data-url$="/delete"]').attr('data-url').replace(/\/[0-9]+\/delete/, '/'+response.category.id+'/delete')) : '';
													} else {
														$form.after(`<div>
															${response.category.name} (reload modal for more options.)
															<hr class="my-1">	
														</div>`);
													}
												}
											} else {
												$.toast({
													heading: 'Error',
													text: response.errors ? response.errors : 'Error',
													showHideTransition: 'slide',
													icon: 'error'
												});
											}
											$btn.removeClass('loading');
										},
										error: function(error) {
											$.toast({
												heading: 'Error',
												text: error.errors ? error.errors : 'Error',
												showHideTransition: 'slide',
												icon: 'error'
											});
											$btn.removeClass('loading');
										}
									});

									return false;
                                },
								delete: function(event) {
									let $btn = $(event.target).closest('a');
									let url = $btn.attr('data-url');
									let data = {'_method': 'DELETE'};

									$btn.addClass('loading');
									$.ajax({
										url: url,
										type: 'POST',
										dataType: "JSON",
										data: data,
										success: function (response) {
											if(response.status) {
												$.toast({
													heading: 'Information',
													text: response.message ? response.message : 'Successfully deleted',
													showHideTransition: 'slide',
													icon: 'info'
												});
												$btn.closest('tr').hide('slow', function(){$btn.remove();});
											} else {
												$.toast({
													heading: 'Error',
													text: response.errors ? response.errors : 'Error',
													showHideTransition: 'slide',
													icon: 'error'
												});
											}
											$btn.removeClass('loading');
										},
										error: function(error) {
											$.toast({
												heading: 'Error',
												text: error.errors ? error.errors : 'Error',
												showHideTransition: 'slide',
												icon: 'error'
											});
											$btn.removeClass('loading');
										}
									});
								}
							};
                        </script>
						<form method="POST" action="{{ route('events.admin.categories.create') }}" accept-charset="UTF-8" onsubmit="return categoryClass.create(event)">
							<div class="input-group mb-3">
								<input type="text" placeholder="New Category" class="form-control" name="category_name">
								<div class="input-group-append">
								  	<button type="submit" class="btn btn-primary">{{ __('events::lang_admin.CREATE_BUTTON') }}</button>
								</div>
							</div>
                        </form>
                        @if($categories->count())
                            <div class="hmtable-wrapper">
                                <div class="hmtable-container o-x-auto o-y-hidden mb-2">
                                    <table id="categories_table">
                                        <thead class="hmtable-list-head noselect">
                                        <tr>
                                            @foreach ($category_fields as $field_name => $data)
                                                <th data-field-name="{{$field_name}}" class="{{ !empty($data['disabled']) ? 'column-disabled' : '' }} {{!empty($data['sticky']) ? 'sticky-enabled active' : ''}}">
                                                    <div data-width="{{isset($data['width']) ? $data['width'] : ''}}" class="sticky-head {{ isset($data['width']) ? 'table-col-width-'.$data['width'] : ''}}">{{ isset($data['title']) ? $data['title'] : '' }}</div>
                                                </th>
                                            @endforeach
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($categories as $key => $category)
                                            <tr data-row-id="{{$category->getKey()}}">
                                                @foreach ($category_fields as $field_name => $data)
                                                    <td class="p-4 {{ !empty($data['disabled']) ? 'column-disabled' : '' }} {{!empty($data['sticky']) ? 'sticky-enabled active' : ''}}" data-field-name="{{ $field_name }}">
                                                    <span class="data-item editable-item">
                                                        <span @if($field_name === 'name' && $category->getKey() !== 1) contenteditable="true" data-url="{{ route('events.admin.categories.edit', ['id' => $category->getKey()]) }}" @endif>
                                                            @switch($field_name)
                                                                @case('name')
                                                                    {{ $category->getName() }}
                                                                    @break
                                                                @case('events_number')
                                                                    {{ $category->events_number }}
                                                                    @break
                                                                @case('date')
                                                                    Created: {{ core()->time()->localize($category->__get('created_at'), true) }}
                                                                    <br>
                                                                    Updated: {{ core()->time()->localize($category->__get('updated_at'), true) }}
                                                                    @break
                                                                @case('actions')
                                                                    @if($category->getKey() !== 1)
                                                                        <a href="javascript:void(0);" title="Edit" onclick="categoryClass.edit(event)"><icon-image data-icon="edit"></icon-image></a>
                                                                        <a href="javascript:void(0);" title="Delete" onclick="categoryClass.delete(event)" data-url="{{ route('events.admin.categories.delete', ['id' => $category->getKey()]) }}"><icon-image data-icon="delete"></icon-image></a>
                                                                    @endif
                                                                    @break
                                                                @default
                                                                    {{ $category->__get($field_name) }}
                                                                    @break
                                                            @endswitch
                                                        </span>
                                                    </span>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    <script> $('#categories_table').hmtable(); </script>
                                </div>
                            </div>
                        @else
                            <span>{{ __('events::lang_admin.NO_CATEGORIES_ALERT') }}</span>
                        @endif
                    </div>
                    <div class="tab-pane fade" id="nav-tags" role="tabpanel" aria-labelledby="nav-tags-tab">
                        <script>
                            var tagClass = {
                                edit: function(event) {
                                    let $btn      = $(event.target);
                                    let $tag_cell = $btn.closest('td').siblings('td[data-field-name="tag"]');
                                    $tag_cell = $tag_cell.find('span span');

                                    $tag_cell.prop('contenteditable', true);
                                    placeCaretAtEnd($tag_cell.get(0));
                                },
                                saveEdited: function($tag) {
                                    let url            = $tag.attr('data-url');
                                    let data = {
                                        '_token': $('meta[name=csrf-token]').attr('content'),
                                        '_method': 'POST',
                                        'tag': $tag.text().trim()
                                    };
                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        dataType: "JSON",
                                        data: data,
                                        success: function (response) {
                                            $tag.data('origin_value', $tag.text().trim());
                                            if(response.status) {
                                                $.toast({
                                                    heading: 'Information',
                                                    text: response.message ? response.message : 'Successfully edited',
                                                    showHideTransition: 'slide',
                                                    icon: 'info'
                                                });
                                            } else {
                                                $.toast({
                                                    heading: 'Error',
                                                    text: response.errors ? response.errors : 'Error',
                                                    showHideTransition: 'slide',
                                                    icon: 'error'
                                                });
                                            }
                                        },
                                        error: function(error) {
                                            $tag.data('origin_value', $tag.text().trim());
                                            $.toast({
                                                heading: 'Error',
                                                text: error.errors ? error.errors : 'Error',
                                                showHideTransition: 'slide',
                                                icon: 'error'
                                            });
                                        }
                                    });
                                },
                                create: function(event) {
                                    let $form = $(event.target);
                                    let $btn  = $form.find('button[type="submit"]');
                                    let url   = $form.attr('action');
                                    let data  = {
                                        '_token': $('meta[name=csrf-token]').attr('content'),
                                        '_method': $form.attr('method'),
                                        'tag': $form.find('input[name="tag"]').val()
                                    };

                                    $btn.addClass('loading');
                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        dataType: "JSON",
                                        data: data,
                                        success: function (response) {
                                            if(response.status) {
                                                $.toast({
                                                    heading: 'Information',
                                                    text: response.message ? response.message : 'Successfully created',
                                                    showHideTransition: 'slide',
                                                    icon: 'info'
                                                });
                                                if(response.tag) {
                                                    if($form.closest('.tab-pane').find('#tags_table tbody tr:last-child').length) {
                                                        let newRow = $form.closest('.tab-pane').find('#tags_table tbody tr:last-child').clone();
                                                        $form.closest('.tab-pane').find('#tags_table tbody tr:last-child').after(newRow);
                                                        newRow.attr('data-row-id', response.tag.id);
                                                        newRow.find('[data-field-name="id"] span span').html(response.tag.id);
                                                        newRow.find('[data-field-name="tag"] span span').html(response.tag.name);
                                                        newRow.find('[data-field-name="tag"] [data-url$="/edit"]').length ? newRow.find('[data-field-name="tag"] [data-url$="/edit"]').attr('data-url', newRow.find('[data-field-name="tag"] [data-url$="/edit"]').attr('data-url').replace(/\/[0-9]+\/edit/, '/'+response.tag.id+'/edit')) : '';
                                                        newRow.find('[data-field-name="events_number"] span span').html(response.tag.events_number);
                                                        newRow.find('[data-field-name="date"] span span').html(response.tag.date);
                                                        newRow.find('[data-field-name="actions"] [data-url$="/delete"]').length ? newRow.find('[data-field-name="actions"] [data-url$="/delete"]').attr('data-url', newRow.find('[data-field-name="actions"] [data-url$="/delete"]').attr('data-url').replace(/\/[0-9]+\/delete/, '/'+response.tag.id+'/delete')) : '';
                                                    } else {
                                                        $form.after(`<div>
															${response.tag.name} (reload modal for more options.)
															<hr class="my-1">
														</div>`);
                                                    }
                                                }
                                            } else {
                                                $.toast({
                                                    heading: 'Error',
                                                    text: response.errors ? response.errors : 'Error',
                                                    showHideTransition: 'slide',
                                                    icon: 'error'
                                                });
                                            }
                                            $btn.removeClass('loading');
                                        },
                                        error: function(error) {
                                            $.toast({
                                                heading: 'Error',
                                                text: error.errors ? error.errors : 'Error',
                                                showHideTransition: 'slide',
                                                icon: 'error'
                                            });
                                            $btn.removeClass('loading');
                                        }
                                    });

                                    return false;
                                },
                                delete: function(event) {
                                    let $btn = $(event.target).closest('a');
                                    let url  = $btn.attr('data-url');
                                    let data = {'_method': 'DELETE'};

                                    $btn.addClass('loading');
                                    $.ajax({
                                        url: url,
                                        type: 'POST',
                                        dataType: "JSON",
                                        data: data,
                                        success: function (response) {
                                            if(response.status) {
                                                $.toast({
                                                    heading: 'Information',
                                                    text: response.message ? response.message : 'Successfully deleted',
                                                    showHideTransition: 'slide',
                                                    icon: 'info'
                                                });
                                                $btn.closest('tr').hide('slow', function(){$btn.remove();});
                                            } else {
                                                $.toast({
                                                    heading: 'Error',
                                                    text: response.errors ? response.errors : 'Error',
                                                    showHideTransition: 'slide',
                                                    icon: 'error'
                                                });
                                            }
                                            $btn.removeClass('loading');
                                        },
                                        error: function(error) {
                                            $.toast({
                                                heading: 'Error',
                                                text: error.errors ? error.errors : 'Error',
                                                showHideTransition: 'slide',
                                                icon: 'error'
                                            });
                                            $btn.removeClass('loading');
                                        }
                                    });
                                }
                            };
                        </script>
                        <form method="POST" action="{{ route('events.admin.tags.create') }}" accept-charset="UTF-8" onsubmit="return tagClass.create(event)">
                            <div class="input-group mb-3">
                                <input type="text" placeholder="New Tag" class="form-control" name="tag">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">{{ __('events::lang_admin.CREATE_BUTTON') }}</button>
                                </div>
                            </div>
                        </form>
                        @if(count($tags))
                            <div class="hmtable-wrapper">
                                <div class="hmtable-container o-x-auto o-y-hidden mb-2">
                                    <table id="tags_table">
                                        <thead class="hmtable-list-head noselect">
                                        <tr>
                                            @foreach ($tag_fields as $field_name => $data)
                                                <th data-field-name="{{$field_name}}" class="{{ !empty($data['disabled']) ? 'column-disabled' : '' }} {{!empty($data['sticky']) ? 'sticky-enabled active' : ''}}">
                                                    <div data-width="{{isset($data['width']) ? $data['width'] : ''}}" class="sticky-head {{ isset($data['width']) ? 'table-col-width-'.$data['width'] : ''}}">{{ isset($data['title']) ? $data['title'] : '' }}</div>
                                                </th>
                                            @endforeach
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($tags as $key => $tag)
                                            <tr data-row-id="{{$tag->id}}">
                                                @foreach ($tag_fields as $field_name => $data)
                                                    <td class="p-3 {{ !empty($data['disabled']) ? 'column-disabled' : '' }} {{!empty($data['sticky']) ? 'sticky-enabled active' : ''}}" data-field-name="{{ $field_name }}">
                                                    <span class="data-item editable-item">
                                                        <span @if($field_name === 'tag' /*&& $category->getKey() !== 1*/) contenteditable="true" data-url="{{ route('events.admin.tags.edit', ['id' => $tag->id]) }}" @endif>
                                                            @switch($field_name)
                                                                @case('tag')
                                                                    {{ $tag->getName() }}
                                                                    @break
                                                                @case('events_number')
                                                                    {{ $tag->events_number }}
                                                                    @break
                                                                @case('actions')
                                                                    <a href="javascript:void(0);"  title="Edit" onclick="tagClass.edit(event)"><icon-image data-icon="edit"></icon-image></a>
                                                                    <a href="javascript:void(0);" title="Delete" onclick="tagClass.delete(event)" data-url="{{ route('events.admin.tags.delete', ['id' => $tag->id]) }}"><icon-image data-icon="delete"></icon-image></a>
                                                                    @break
                                                                @default
                                                                    @break
                                                            @endswitch
                                                        </span>
                                                    </span>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    <script> $('#tags_table').hmtable(); </script>
                                </div>
                            </div>
                        @else
                            <span>{{ __('events::lang_admin.NO_TAGS_ALERT') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>