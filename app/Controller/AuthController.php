<?php
/**
 * Auth Controller
 *
 * @copyright	(c) 2015-present Passbolt.com
 * @licence		GNU Affero General Public License http://www.gnu.org/licenses/agpl-3.0.en.html
 */
class AuthController extends AppController {

/**
 * Called before the controller action. Used to manage access right
 *
 * @return void
 * @link http://book.cakephp.org/2.0/en/controllers.html#request-life-cycle-callbacks
 */
	public function beforeFilter() {
		$this->Auth->allow();
		parent::beforeFilter();
	}

/**
 * Login
 *
 * @return void
 */
	public function login() {
		// check if the user Authentication worked
		if (!$this->Auth->login()) {
			$this->layout = 'login';
			$this->view = '/Auth/login';
		} else {
			if ($this->request->is('json')) {
				// We do not redirect since the Javascript app will take care of this
				// Also it messes up with the GPGAuth headers if we do
			} else {
				return $this->redirect($this->Auth->redirectUrl());
			}
		}
	}

/**
 * Triggers GPGAuth first step, e.g. server key verification
 *
 * @return void
 */
	public function verify() {
		if ($this->request->is('post')) {
			$this->Auth->login();
		} else {
			$key['fingerprint'] = Configure::read('GPG.serverKey.fingerprint');
			$file = new File(Configure::read('GPG.serverKey.public'));
			if ($file->exists()) {
				$key['keydata'] = $file->read();
				$this->set('data', $key);
				if (!$this->request->is('json')) {
					$this->layout = 'empty';
				}
				return $this->Message->success();
			} else {
				return $this->Message->error(
					__('The public key for this passbolt instance was not found.'),
					array('code' => '400')
				);
			}
		}
	}

/**
 * Logout
 *
 * @return void
 */
	public function logout() {
		$this->redirect($this->Auth->logout());
	}

/**
 * Used to return partial login components to be used by the plugin to update the login page
 *
 * @param string $case the element to render
 * @return bool success
 */
	public function partials($case) {
		if ($this->request->isAjax()) {
			$allowed = array(
				'default', 'noconfig', 'stage0'
			);
			foreach ($allowed as $c) {
				if ($c === $case) {
					$this->render('../Elements/public/Auth/' . $case);
					return true;
				}
			}
			$this->render('../Elements/public/Auth/default');
			return true;
		}
		$this->redirect('/auth/login');
		return false;
	}
}