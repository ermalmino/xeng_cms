<?php

namespace AppBundle\Controller\Admin;

use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Admin\Account\AccountUserEditHandler;
use AppBundle\Form\Admin\Account\UserProfileEditHandler;
use AppBundle\Entity\Account\Profile;
use AppBundle\Entity\Auth\XUser;
use AppBundle\Services\Account\ProfileManager;
use AppBundle\Services\Auth\XUserManager;

/**
 * @author Ermal Mino <ermal.mino@gmail.com>
 *
 * @Route("/admin")
 * @Security("is_authenticated()")
 */
class AccountAdminController extends Controller {
    /**
     * @Route("/account", name="xeng.admin.account")
     *
     * @return Response
     */
    public function accountAction() {
        return $this->redirectToRoute('xeng.admin.account.user');
    }

    /**
     * @Route("/account/user", name="xeng.admin.account.user")
     *
     * @param Request $request
     * @param XUserManager $xUserManager
     * @return Response
     */
    public function accountUserAction(Request $request, XUserManager $xUserManager) {
        /** @var XUser $user */
        $user=$this->getUser();

        $formHandler = new AccountUserEditHandler($this->container,$request,$user);
        $formHandler->handle();

        $validationResponse=$formHandler->getValidationResponse();

        if($formHandler->isSubmitted() && $formHandler->isValid()){
            $newPassword=null;
            if(!$validationResponse->getParam('password')->isEmpty()){
                $newPassword=$validationResponse->getStringValue('password');
            }
            $xUserManager->updateUser($user->getId(),
                $validationResponse->getValue('username'),
                $validationResponse->getValue('email'),
                $validationResponse->getBooleanValue('enabled'),
                $newPassword
            );

            $this->addFlash(
                'notice',
                'The user was updated succesfully!'
            );
        }

        return $this->render('admin/account/accountUser.html.twig', array(
            'validationResponse' => $validationResponse
        ));
    }


    /**
     * @Route("/account/profile", name="xeng.admin.account.profile")
     *
     * @param Request $request
     * @param ProfileManager $profileManager
     * @return Response
     * @throws NonUniqueResultException
     */
    public function editUserProfileAction(Request $request, ProfileManager $profileManager) {
        /** @var XUser $user */
        $user=$this->getUser();

        $profile=$profileManager->getProfileByUser($user->getId());
        $newProfile=false;
        if(!$profile){
            $profile=new Profile();
            $newProfile=true;
        }

        $formHandler = new UserProfileEditHandler($this->container,$request,$profile);
        $formHandler->handle();

        $validationResponse=$formHandler->getValidationResponse();

        if($formHandler->isSubmitted() && $formHandler->isValid()){
            $p=null;
            if($newProfile){
                $p=$profileManager->createProfile(
                    $user,
                    $validationResponse->getValue('firstName'),
                    $validationResponse->getValue('lastName')
                );
                $newProfile=false;
                $this->addFlash(
                    'notice',
                    'Profile created successfully!'
                );
            } else {
                $p=$profileManager->updateProfile(
                    $profile->getId(),
                    $validationResponse->getValue('firstName'),
                    $validationResponse->getValue('lastName')
                );
                $this->addFlash(
                    'notice',
                    'Profile updated successfully!'
                );
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile=$validationResponse->getValue('profileImage');
            if($p && $uploadedFile){
                $profile=$p;
                $profileManager->updateProfileImage($p,$uploadedFile);
            }

        }

        return $this->render('admin/account/accountProfile.html.twig', array(
            'user' => $user,
            'newProfile' => $newProfile,
            'profile' => $profile,
            'validationResponse' => $validationResponse
        ));
    }

}
