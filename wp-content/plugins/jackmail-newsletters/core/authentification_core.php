<?php


class Jackmail_Authentification_Core {

	protected function account_creation( $email, $password, $firstname, $lastname ) {
		if ( is_email( $email ) ) {
			$url      = $this->core->get_jackmail_url_identity() . 'users';
			$headers  = array(
				'content-type' => 'application/json'
			);
			$body     = array(
				'email'       => $email,
				'password'    => $password,
				'firstName'   => $firstname,
				'lastName'    => $lastname,
				'locale'      => $this->core->get_current_language(),
				'url'         => get_home_url(),
				'accountType' => 'JACKMAIL'
			);
			$timeout  = 30;
			$response = $this->core->remote_post( $url, $headers, $body, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['body'] ) ) {
					$data = json_decode( $response['body'], true );
					if ( isset( $data['email'] ) ) {
						$url  = $this->core->get_jackmail_url_ws() . 'create-user.php';
						$body = array(
							'email' => $email
						);
						$this->core->remote_post( $url, $headers, $body, $timeout );
						return true;
					}
				}
			}
		}
		return false;
	}

	protected function account_connection( $email, $password ) {
		$json = array(
			'success' => false,
			'message' => ''
		);
		if ( is_email( $email ) && $password !== '' ) {
			$url      = $this->core->get_jackmail_url_identity() . 'authenticate';
			$headers  = array(
				'content-type' => 'application/json'
			);
			$body     = array(
				'email'    => $email,
				'password' => $password
			);
			$timeout  = 30;
			$response = $this->core->remote_post( $url, $headers, $body, $timeout );
			if ( is_array( $response ) ) {
				if ( isset( $response['response'] ) ) {
					if ( is_array( $response['response'] ) ) {
						if ( isset( $response['response']['code'] ) ) {
							if ( $response['response']['code'] === 403 ) {
								$json['message'] = 'NOT_ACTIVED';
							} else {
								if ( isset( $response['body'] ) ) {
									$data = json_decode( $response['body'], true );
									if ( isset( $data['token'], $data['accounts'], $data['lastName'], $data['firstName'] ) ) {
										$token     = $data['token'];
										$accounts  = $data['accounts'];
										$user_id   = $data['email'];
										$lastname  = $data['lastName'];
										$firstname = $data['firstName'];
										if ( is_array( $accounts ) ) {
											if ( count( $accounts ) > 0 ) {
												if ( isset( $accounts[0]['accountId'] ) ) {
													$account_id         = $accounts[0]['accountId'];
													$current_account_id = $this->core->get_account_id();
													$current_user_id    = $this->core->get_user_id();
													if ( ( $current_account_id === '' && $current_user_id === '' )
													     || ( $current_account_id === $account_id && $current_user_id === $user_id ) ) {
														$this->core->set_account_token( $token );
														if ( $current_account_id === '' ) {
															$this->core->set_account_id( $account_id );
														}
														if ( $current_user_id === '' ) {
															$this->core->set_user_id( $user_id );
														}
														$this->core->set_lastname( $lastname );
														$this->core->set_firstname( $firstname );
														$json = array(
															'success' => true,
															'message' => 'OK'
														);
														update_option( 'jackmail_authentification_failed', '0' );
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $json;
	}

	protected function account_info() {
		return $this->core->get_account_info();
	}

	protected function account_reset( $email ) {
		if ( is_email( $email ) ) {
			$url     = $this->core->get_jackmail_url_identity() . 'users/' . urlencode( $email ) . '/reset';
			$headers = array(
				'content-type' => 'application/json'
			);
			$body    = array();
			$timeout = 30;
			$this->core->remote_post( $url, $headers, $body, $timeout );
			return true;
		}
		return false;
	}

	protected function account_resend_activation_email( $email, $password ) {
		if ( is_email( $email ) ) {
			$url     = $this->core->get_jackmail_url_identity() . 'users/reactivate';
			$headers = array(
				'content-type' => 'application/json'
			);
			$body    = array(
				'accountType' => 'jackmail',
				'email'       => $email,
				'password'    => $password
			);
			$timeout = 30;
			$this->core->remote_post( $url, $headers, $body, $timeout );
			return true;
		}
		return false;
	}

}