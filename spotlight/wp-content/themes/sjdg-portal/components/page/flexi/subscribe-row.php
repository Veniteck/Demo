<section class="section section--newsletter">

	<div class="shell">

		<header class="section__head">
			<h4><?php the_sub_field( 'subscribe_form_row_title' ); ?></h4>
			<p><?php the_sub_field( 'subscribe_form_row_description' ); ?></p>
		</header><!-- /.section__head -->

		<div class="section__body">

			<div class="form form--newsletter">

				<?php $form = get_sub_field( 'subscribe_form_row_gravity_form_id' ); ?>

				<?php gravity_form( $form['id'], false, false, false, null, true ); ?>

				<?php /**
				<div class="gf_browser_chrome gform_wrapper" id="gform_wrapper_2406">
					<form method="post" enctype="multipart/form-data" id="gform_2406"
					      action="/?gf_page=preview&amp;id=2406">
						<div class="gform_heading">
							<h3 class="gform_title">Waterman--Subscribe</h3>
							<span class="gform_description"></span>
						</div>
						<div class="gform_body">
							<ul id="gform_fields_2406"
							    class="gform_fields top_label form_sublabel_below description_below">
								<li id="field_2406_1"
								    class="gfield gfield_contains_required field_sublabel_below field_description_below gfield-text gfield-medium gfield-text">
									<label class="gfield_label" for="input_2406_1">Name<span
											class="gfield_required">*</span></label>
									<div class="ginput_container ginput_container_text"><input name="input_1"
									                                                           id="input_2406_1"
									                                                           type="text" value=""
									                                                           class="medium"
									                                                           tabindex="1"
									                                                           placeholder="Name">
									</div>
								</li>
								<li id="field_2406_2"
								    class="gfield gfield_contains_required field_sublabel_below field_description_below gfield-email gfield-medium gfield-email">
									<label class="gfield_label" for="input_2406_2">Email<span
											class="gfield_required">*</span></label>
									<div class="ginput_container ginput_container_email">
										<input name="input_2" id="input_2406_2" type="email" value="" class="medium"
										       tabindex="2" placeholder="Email">
									</div>
								</li>
							</ul>
						</div>
						<div class="gform_footer top_label"><input type="submit" id="gform_submit_button_2406"
						                                           class="gform_button button" value="SUBSCRIBE"
						                                           tabindex="3"
						                                           onclick="if(window[&quot;gf_submitting_2406&quot;]){return false;}  if( !jQuery(&quot;#gform_2406&quot;)[0].checkValidity || jQuery(&quot;#gform_2406&quot;)[0].checkValidity()){window[&quot;gf_submitting_2406&quot;]=true;}  ">
							<input type="hidden" class="gform_hidden" name="is_submit_2406" value="1">
							<input type="hidden" class="gform_hidden" name="gform_submit" value="2406">
							<input type="hidden" class="gform_hidden" name="gform_unique_id" value="">
							<input type="hidden" class="gform_hidden" name="state_2406"
							       value="WyJbXSIsIjUzODk4MzJjNjRhNjQ3OGJkNTZkYTZkMjczNDZhNTg4Il0=">
							<input type="hidden" class="gform_hidden" name="gform_target_page_number_2406"
							       id="gform_target_page_number_2406" value="0">
							<input type="hidden" class="gform_hidden" name="gform_source_page_number_2406"
							       id="gform_source_page_number_2406" value="1">
							<input type="hidden" name="gform_field_values" value="">
						</div>
					</form>
				</div> */ ?>

			</div><!-- /.form form-/-newsletter -->

		</div><!-- /.section__body -->

	</div><!-- /.shell -->

</section><!-- /.section section-/-newsletter -->
