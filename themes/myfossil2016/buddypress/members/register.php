
<div id="buddypress" class="container">

	<?php do_action( 'bp_before_register_page' ); ?>

	<div class="page" id="register-page">

		<h4>Register</h4>
		
		<form action="" name="signup_form" id="signup_form" class="standard-form" method="post" enctype="multipart/form-data" novalidate>

		<?php if ( 'registration-disabled' == bp_get_current_signup_step() ) : ?>
			<?php do_action( 'template_notices' ); ?>
			<?php do_action( 'bp_before_registration_disabled' ); ?>

				<p><?php _e( 'User registration is currently not allowed.', 'buddypress' ); ?></p>

			<?php do_action( 'bp_after_registration_disabled' ); ?>
		<?php endif; // registration-disabled signup setp ?>

		<?php if ( 'request-details' == bp_get_current_signup_step() ) : ?>

			<?php do_action( 'template_notices' ); ?>

			<?php do_action( 'bp_before_account_details_fields' ); ?>









<?php if ( bp_is_active( 'xprofile' ) ) : ?>

				<?php do_action( 'bp_before_signup_profile_fields' ); ?>

				<div class="register-section" id="profile-details-section">
					
					<?php /* Use the profile field loop to render input fields for the 'base' profile field group */ ?>
					<?php 
					global $profile_template;
					global $group;
					if ( bp_is_active( 'xprofile' ) ) : 
						if ( bp_has_profile( array( 'fetch_field_data' => false ) ) ) : 
						//var_dump($profile_template);
							while ( bp_profile_groups() ) : 
								bp_the_profile_group(); 
								//var_dump($group);
								if (in_array( $group->name, array('age', 'guardian' ) ) ) :
									echo "<div class='xprofile-group-".$group->name."'>";
									while ( bp_profile_fields() ) : 
										bp_the_profile_field(); 
										//13 & 14 are for everyone, 25 is adults only, 349,350,351 are child only,the rest are consenting only
										$field_classes = 'editfield';
										switch ( bp_get_the_profile_field_id() ) {
											case 343 :
											case 13 :
											case 14 :
												break;
											case 346 :
												$field_classes .= ' minor';
												break;
											case 349 :
											case 350 :
											case 351 :
												$field_classes .= ' consent-minor';
												break;
											case 25 :
												$field_classes .= ' adult';
												break;
											default :
												$field_classes .= ' adult-consent-minor';
												break;
										}
										//$field_classes .= ( bp_get_the_profile_field_id() == 25 ) ? 'consent_only adult_only' : ( !in_array( bp_get_the_profile_field_id(), array( 13, 14 ) ) ) ? "consent_only" : "";
										?>

										<div<?php bp_field_css_class( $field_classes ); ?>>
										<?php if ( bp_get_the_profile_field_id() == 346 ) : ?>
										<p><br /><img src="/wp-content/uploads/avatars/8/5794f050ceb088689c7fd8c88ed47bb2-bpfull.jpg" alt="bruce" /><br />
											Bruce MacFadden<br />
											University of Florida<br />
											Florida Museum of Natural History<br /><br />
											Hello,<br />
											My name is Bruce MacFadden and I am a professor at the University of Florida. I represent a small group
											of researchers who are building and studying myFOSSIL in order to learn about how people understand
											paleontology when they use social technologies, like apps and websites. If you decide to participate, you
											will be asked to answer a few questions about your experiences and interests concerning science and
											social media. This will take about 5 minutes.<br /><br />
											There are no known risks to participation, and most people actually enjoy talking about their
											experiences with technology. You do not have to be in this study if you don&#39;t want to and you can quit
											the study at any time. Other than our research team, no one will know your answers, including your
											teachers or your classmates. If you don&#39;t like a question, you don&#39;t have to answer it and, if you ask, your
											answers will not be used in the study.<br /><br />

											If you are willing to participate, we must also get an OK from one of your parents or guardians.<br /></p>
										<?php endif; ?>

										<?php
										$field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );
										$field_type->edit_field_html();
			
										do_action( 'bp_custom_profile_edit_fields_pre_visibility' );
										?>
										
										<?php do_action( 'bp_custom_profile_edit_fields' ); ?>

										</div>
							
									<?php endwhile;
									echo "</div>"; 
								endif;
							endwhile;
						endif;
					endif;

					do_action( 'bp_signup_profile_fields' ); ?>

				</div><!-- #profile-details-section -->

				<?php do_action( 'bp_after_signup_profile_fields' ); ?>

			<?php endif; ?>

			
			<div class="register-section" id="basic-details-section">

				<?php /***** Basic Account Details ******/ ?>

				<label for="signup_username"><?php _e( 'User Name', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
				<?php do_action( 'bp_signup_username_errors' ); ?>
				<input type="text" name="signup_username" id="signup_username" value="<?php bp_signup_username_value(); ?>" />
				
				<label for="signup_email"><?php _e( 'Your Email', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
				<?php do_action( 'bp_signup_email_errors' ); ?>
				<input type="text" name="signup_email" id="signup_email" value="<?php bp_signup_email_value(); ?>" />
				
				<label for="signup_password"><?php _e( 'Choose a Password', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
				<?php do_action( 'bp_signup_password_errors' ); ?>
				<input type="password" name="signup_password" id="signup_password" value="" class="password-entry" />
				<div id="pass-strength-result"></div>
				
				<label for="signup_password_confirm"><?php _e( 'Confirm Password', 'buddypress' ); ?> <?php _e( '(required)', 'buddypress' ); ?></label>
				<?php do_action( 'bp_signup_password_confirm_errors' ); ?>
				<input type="password" name="signup_password_confirm" id="signup_password_confirm" value="" class="password-entry-confirm" />

				<?php do_action( 'bp_account_details_fields' ); ?>

			</div><!-- #basic-details-section -->

			<?php do_action( 'bp_after_account_details_fields' ); ?>

			<?php /***** Extra Profile Details ******/ ?>

			<?php if ( bp_is_active( 'xprofile' ) ) : ?>

				<?php do_action( 'bp_before_signup_profile_fields' ); ?>

				<div class="register-section" id="profile-details-section">
					
					<?php /* Use the profile field loop to render input fields for the 'base' profile field group */ ?>
					<?php if ( bp_is_active( 'xprofile' ) ) : if ( bp_has_profile( array( 'fetch_field_data' => false ) ) ) : while ( bp_profile_groups() ) : bp_the_profile_group();
						if (!in_array( $group->name, array('age', 'guardian' ) ) ) : ?>

					<?php while ( bp_profile_fields() ) : bp_the_profile_field();  
						//13 & 14 are for everyone, 25 is adults only, 349,350,351 are child only,the rest are consenting only
						$field_classes = 'editfield';
						switch ( bp_get_the_profile_field_id() ) {
							case 343 :
							case 13 :
							case 14 :
								break;
							case 346 :
								$field_classes .= ' minor';
								break;
							case 349 :
							case 350 :
							case 351 :
								$field_classes .= ' consent-minor';
								break;
							case 25 :
								$field_classes .= ' adult';
								break;
							default :
								$field_classes .= ' adult-consent-minor';
								break;
						}
					//$field_classes .= ( bp_get_the_profile_field_id() == 25 ) ? 'consent_only adult_only' : ( !in_array( bp_get_the_profile_field_id(), array( 13, 14 ) ) ) ? "consent_only" : "";
					?>
	
							<div<?php bp_field_css_class( $field_classes ); ?>>
								
<?php if (bp_get_the_profile_field_id() == 25) : ?>


				<br />&nbsp;<br />
				<label>Description and Informed Consent</label>
				<br/>
				<textarea readonly rows="10" cols="40" style="width:100%">
Study Description

FOSSIL is creating a national network of fossil clubs and professional paleontologists. The project team is very interested in your experiences, interests, needs, and feedback about the development and effectiveness of FOSSIL in creating a network for amateur and professional paleontologists and providing opportunities for members to learn science, contribute to science, and promote informal science learning. Participant feedback informs the on-going project development and implementation of FOSSIL. 

Procedure: 
If you choose to participate in this study, you are allowing us to use information that is collected during the normal, iterative development activities for FOSSIL. The following types of information may be collected:

Survey: 
A variety of means may be used to document your perspective, experiences, interests, and needs.

Artifacts: 
A variety of artifacts may be collected to document your perspective, experiences, interests, and needs. These may include but are not limited to communication artifacts.

Online archives: 
Comments and responses that are posted on the myFOSSIL website. 

Observations: 
Observation data in the form of field notes, video recordings, or audio recordings may be collected to document your interactions during focus groups, workshops, or the annual FOSSIL meeting. 

Interviews: 
Interviews lasting approximately 20 minutes may be conducted to gain insight into your perspective, experiences, interests, and needs. Interviews will be audio or video recorded and transcribed. Interviews will focus on the following kinds of questions:
Describe your participation in FOSSIL.
What elements of FOSSIL are most effective in creating a network among amateur and professional paleontologists? Please explain.
How do digitized collections impact the practices of the network? 
How does your participation in FOSSIL influence your science learning, contributions to the science of paleontology, and promotion of informal science education? 
What types of components should we include in the myFOSSIL website? Please explain. 
Are the FOSSIL programmatic activities and training effective? Please explain. 
Is the communication among FOSSIL participants effective? Please explain. 
How do the FOSSIL amateur and professional paleontologists share practices and skills?
How does networking the clubs improve the outreach, resources, and capabilities of the individual clubs? 
An anonymous coding scheme will be applied to all information collected and prior to analysis. Data will be analyzed and reported in an aggregated fashion and participants will not be identified by name in any reports of our research.

Risks and Benefits of Participation:
There are risks involved in all research and evaluation studies. However, minimal risk is envisioned for participating in this project. You will not be identified by name in any reports of this research; pseudonyms will be used. There are no direct benefits for participating in the FOSSIL project research and evaluation. However, future participants may experience benefits in the form of a more effective FOSSIL project in providing a network for amateur and professional paleontologists that fosters science learning, contributions to the science of paleontology, and informal science learning. 

Time Required and Compensation:
The study will occur over the course of the FOSSIL project and we anticipate requiring a total of 60 minutes annually of your time to complete the surveys. There will be no compensation for participating in this study.

Confidentiality:
All information gathered in this study will be kept confidential to the extent provided by law. No reference will be made in written or oral materials that could link you to this study. All physical records will be stored in a locked file cabinet in the Principal Investigator's office. Only the researchers will have access to the information we collect online. There is a minimal risk that security of any online data may be breached, but since no identifying information will be collected, and the online host uses several forms of encryption and other protections, it is unlikely that a security breach of the online data will result in any adverse consequence for you. When the study is completed and the data have been analyzed, the information will be shredded and/or electronically erased.

Voluntary Participation:
Your participation is strictly voluntary. Non-participation or denied consent to collect some or all of the data listed above will not affect your participation in FOSSIL. In addition, you may request at any time that your data not to be included.

Contact Information:
If you have any questions or concerns about the study, you may contact Dr. Kent Crippen at kcrippen@coe.ufl.edu or (352) 273-4222 or Dr. Betty Dunckel at bdunckel@flmnh.ufl.edu. For questions regarding your rights as a research participant in this study you may contact the UFIRB Office, Box 112250, University of Florida, Gainesville, FL 32611-2250; ph (352) 392-0433.

Participant Consent:
I have read the above information and agree to participate in this study. I am at least 18 years of age.			
			
				</textarea>
			
				<br/>
				<a href="<?php echo site_url('FOSSIL-Project-Description-Informed-Consent.pdf'); ?>" download>Download the Project Description and Informed Consent Document</a>
			



<?php endif; ?>
	
								<?php
								$field_type = bp_xprofile_create_field_type( bp_get_the_profile_field_type() );
								$field_type->edit_field_html();
	
								do_action( 'bp_custom_profile_edit_fields_pre_visibility' );
								?>
								
								<?php do_action( 'bp_custom_profile_edit_fields' ); ?>

							</div>
						
					<?php endwhile; endif; ?>

					<?php endwhile; ?>


					<input type="hidden" name="signup_profile_field_ids" id="signup_profile_field_ids" value="<?php bp_the_profile_field_ids(); ?>" />
				<?php endif; endif; ?>
					
													
					
					<?php do_action( 'bp_signup_profile_fields' ); ?>

				</div><!-- #profile-details-section -->

				<?php do_action( 'bp_after_signup_profile_fields' ); ?>

			<?php endif; ?>


			<?php do_action( 'bp_before_registration_submit_buttons' ); ?>

			<div class="submit">
				<br/>
				<input type="submit" name="signup_submit" id="signup_submit" value="<?php esc_attr_e( 'Register', 'buddypress' ); ?>" />
			</div>

			<?php do_action( 'bp_after_registration_submit_buttons' ); ?>

			<?php wp_nonce_field( 'bp_new_signup' ); ?>

			<br/>
			
		<?php endif; // request-details signup step ?>

		
		
		<?php if ( 'completed-confirmation' == bp_get_current_signup_step() ) : ?>
			<?php do_action( 'template_notices' ); ?>
			<?php do_action( 'bp_before_registration_confirmed' ); ?>

			<?php if ( bp_registration_needs_activation() ) : ?>
				<p><?php _e( 'You have successfully created your account! To begin using this site you will need to activate your account via the email we have just sent to your address.', 'buddypress' ); ?></p>
			<?php else : ?>
				<p><?php _e( 'You have successfully created your account! Please log in using the username and password you have just created.', 'buddypress' ); ?></p>
			<?php endif; ?>

			<?php do_action( 'bp_after_registration_confirmed' ); ?>

		<?php endif; // completed-confirmation signup step ?>

		<?php do_action( 'bp_custom_signup_steps' ); ?>

		</form>
		<p>By registering for the myFOSSIL site you are agreeing to our <a href="https://www.myfossil.org/terms-of-service/" target="_BLANK">Terms of Service</a>.</p>

	</div>

	<?php do_action( 'bp_after_register_page' ); ?>

</div><!-- #buddypress -->