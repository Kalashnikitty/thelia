<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace Thelia\Controller\Admin;

use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\AccessManager;
use Thelia\Form\ConfigStoreForm;
use Thelia\Model\ConfigQuery;

/**
 * Class ConfigStoreController
 * @package Thelia\Controller\Admin
 * @author Christophe Laffont <claffont@openstudio.fr>
 */
class ConfigStoreController extends BaseAdminController
{
    protected function renderTemplate()
    {
        return $this->render('config-store');
    }

    public function defaultAction()
    {
        if (null !== $response = $this->checkAuth(AdminResources::STORE, array(), AccessManager::VIEW)) {
            return $response;
        }

        // The form is self-hydrated
        $configStoreForm = new ConfigStoreForm($this->getRequest(), 'form');

        $this->getParserContext()->addForm($configStoreForm);

        return $this->renderTemplate();
    }

    public function saveAction()
    {
        if (null !== $response = $this->checkAuth(AdminResources::STORE, array(), AccessManager::UPDATE)) {
            return $response;
        }

        $error_msg = false;
        $response = null;
        $configStoreForm = new ConfigStoreForm($this->getRequest());

        try {
            $form = $this->validateForm($configStoreForm);

            $data = $form->getData();

            // Update store
            foreach ($data as $name => $value) {
                if (! in_array($name, array('success_url', 'error_message'))) {
                    ConfigQuery::write($name, $value, false);
                }
            }

            $this->adminLogAppend(AdminResources::STORE, AccessManager::UPDATE, "Store configuration changed");

            if ($this->getRequest()->get('save_mode') == 'stay') {
                $response = $this->generateRedirectFromRoute('admin.configuration.store.default');
            } else {
                $response = $this->generateSuccessRedirect($configStoreForm);
            }
        } catch (\Exception $ex) {
            $error_msg = $ex->getMessage();
        }

        if (false !== $error_msg) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("Store configuration failed."),
                $error_msg,
                $configStoreForm,
                $ex
            );

            $response = $this->renderTemplate();
        }

        return $response;
    }
}
