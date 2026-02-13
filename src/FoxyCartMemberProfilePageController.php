<?php

namespace Dynamic\FoxyStripeMembers;

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\Form;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Symbiote\MemberProfiles\Pages\MemberProfilePageController;

class FoxyCartMemberProfilePageController extends MemberProfilePageController
{
    /**
     * @return array|HTTPResponse
     */
    public function index(HTTPRequest $request)
    {
        $backURL = $request->getVar('BackURL');
        if ($backURL) {
            $request->getSession()->set('MemberProfile.REDIRECT', $backURL);
        }
        $member = Security::getCurrentUser();
        $mode = $member ? 'profile' : 'register';
        $data = $member ? $this->indexProfile() : $this->indexRegister();
        if (is_array($data)) {
            return $this->customise($data)->renderWith([
                'FoxyCartMemberProfilePage_' . $mode,
                FoxyCartMemberProfilePage::class ,
                'Page',
            ]);
        }
        return $data;
    }

    /**
     * Handles validation and saving new Member objects, as well as sending out validation emails.
     */
    public function register($data, Form $form)
    {
        if ($member = $this->addMember($form)) {
            if (!$this->RequireApproval && $this->EmailType != 'Validation' && !$this->AllowAdding) {
                if ($member->canLogin()) {
                    Injector::inst()->get(IdentityStore::class)->logIn($member);
                }
            }

            if (isset($data['backURL']) && Director::is_site_url($data['backURL'])) {
                return $this->redirect($data['backURL']);
            }

            if ($this->RegistrationRedirect) {
                if ($this->PostRegistrationTargetID) {
                    return $this->redirect($this->PostRegistrationTarget()->Link());
                }

                $request = $this->getRequest();
                $sessionTarget = $request->getSession()->get('MemberProfile.REDIRECT');
                if ($sessionTarget) {
                    $request->getSession()->clear('MemberProfile.REDIRECT');
                    if (Director::is_site_url($sessionTarget)) {
                        return $this->redirect($sessionTarget);
                    }
                }
            }

            return $this->redirect($this->Link('afterregistration'));
        } else {
            return $this->redirectBack();
        }
    }
}
