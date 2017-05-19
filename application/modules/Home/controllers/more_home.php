<?php
//New  Modules

						if (pt_main_module_available('activity')) {
								$activeModules[] = "activity";
								$this->load->library('Activity/Activity_lib');
								$this->data['activitylib'] = $this->Activity_lib;
								$this->data['locationsList'] = $this->Activity_lib->getLocationsList();
								$this->data['featuredActivity'] = $this->Activity_lib->getFeaturedActivity();
								$this->data['popularActivity'] = $this->Activity_lib->getTopRatedActivity();
								$this->data['moduleTypes'] = $this->Activity_lib->activityTypes();
								$this->data['checkin'] = $this->Activity_lib->date;
								$this->data['adults'] = $this->Activity_lib->adults;
								$this->data['child'] = (int) $this->Activity_lib->child;
								$this->data['featuredSection']['modules']["activity"] = (object)array("featured" => $this->Activity_lib->getFeaturedActivity(),'moduleTitle' => trans('Activity'), 'bgImg' => 'featured-activity.jpg', 'booknowClass' => 'warning','featuredText' => trans('0451'), 'featuredPrice' => 200,'currCode' => 'USD');
								$this->data['activityLocations'] = $this->Activity_lib->activityByLocations();
								$this->load->helper("Activity/activity_front");
								$this->load->model('Activity/Activity_model');
						}



// Modules  Restaurant
																		if (pt_main_module_available('restaurant')) {
																				$activeModules[] = "restaurant";
																				$this->load->library('Restaurant/Restaurant_lib');
																				$this->data['restaurantlib'] = $this->Restaurant_lib;
																				$this->data['locationsList'] = $this->Restaurant_lib->getLocationsList();
																				$this->data['featuredRestaurant'] = $this->Restaurant_lib->getFeaturedRestaurant();
																				$this->data['popularRestaurant'] = $this->Restaurant_lib->getTopRatedRestaurant();
																				$this->data['moduleTypes'] = $this->Restaurant_lib->restaurantTypes();
																				$this->data['checkin'] = $this->Restaurant_lib->date;
																				$this->data['adults'] = $this->Restaurant_lib->adults;
																				$this->data['child'] = (int) $this->Restaurant_lib->child;
																				$this->data['featuredSection']['modules']["restaurant"] = (object)array("featured" => $this->Restaurant_lib->getFeaturedRestaurant(),'moduleTitle' => trans('Restaurant'), 'bgImg' => 'featured-restaurant.jpg', 'booknowClass' => 'warning','featuredText' => trans('0451'), 'featuredPrice' => 200,'currCode' => 'USD');
																				$this->data['tourLocations'] = $this->Restaurant_lib->restaurantByLocations();
																				$this->load->helper("Restaurant/restaurant_front");
																				$this->load->model('Restaurant/Restaurant_model');
																		}

//Modules SPA
																								if (pt_main_module_available('spa')) {
																										$activeModules[] = "spa";
																										$this->load->library('Spa/Spa_lib');
																										$this->data['spalib'] = $this->Spa_lib;
																										$this->data['locationsList'] = $this->Spa_lib->getLocationsList();
																										$this->data['featuredSpa'] = $this->Spa_lib->getFeaturedSpa();
																										$this->data['popularSpa'] = $this->Spa_lib->getTopRatedSpa();
																										$this->data['moduleTypes'] = $this->Spa_lib->spaTypes();
																										$this->data['checkin'] = $this->Spa_lib->date;
																										$this->data['adults'] = $this->Spa_lib->adults;
																										$this->data['child'] = (int) $this->Spa_lib->child;
																										$this->data['featuredSection']['modules']["spa"] = (object)array("featured" => $this->Spa_lib->getFeaturedSpa(),'moduleTitle' => trans('Spa'), 'bgImg' => 'featured-spa.jpg', 'booknowClass' => 'warning','featuredText' => trans('0451'), 'featuredPrice' => 200,'currCode' => 'USD');
																										$this->data['spaLocations'] = $this->Spa_lib->spaByLocations();
																										$this->load->helper("Spa/spa_front");
																										$this->load->model('Spa/Spa_model');
																								}

//Modules  Wedding
																														if (pt_main_module_available('wedding')) {
																																$activeModules[] = "wedding";
																																$this->load->library('Wedding/Wedding_lib');
																																$this->data['weddinglib'] = $this->Wedding_lib;
																																$this->data['locationsList'] = $this->Wedding_lib->getLocationsList();
																																$this->data['featuredWedding'] = $this->Wedding_lib->getFeaturedWedding();
																																$this->data['popularWedding'] = $this->Wedding_lib->getTopRatedWedding();
																																$this->data['moduleTypes'] = $this->Wedding_lib->weddingTypes();
																																$this->data['checkin'] = $this->Wedding_lib->date;
																																$this->data['adults'] = $this->Wedding_lib->adults;
																																$this->data['child'] = (int) $this->Wedding_lib->child;
																																$this->data['featuredSection']['modules']["wedding"] = (object)array("featured" => $this->Wedding_lib->getFeaturedWedding(),'moduleTitle' => trans('Wedding'), 'bgImg' => 'featured-wedding.jpg', 'booknowClass' => 'warning','featuredText' => trans('0451'), 'featuredPrice' => 200,'currCode' => 'USD');
																																$this->data['weddingLocations'] = $this->Wedding_lib->weddingByLocations();
																																$this->load->helper("Wedding/wedding_front");
																																$this->load->model('Wedding/Wedding_model');
																														}
//End Modules

?>
