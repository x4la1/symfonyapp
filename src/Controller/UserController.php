<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Infrastructure\Config;
use App\Repository\UserRepository;
use App\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UserController extends AbstractController
{
    private const VALID_IMAGE_TYPES = ["image/gif", "image/png", "image/jpeg"];
    private const DATE_TIME_FORMAT = 'Y-m-d';

    public function __construct(
        private UserRepository $userRepository,
    )
    {
    }

    public function index(): Response
    {
        return $this->render('/registration/register_user_form.html.twig');
    }

    public function registerUser(Request $request): ?Response
    {
        $user = $this->makeUserFromRequest($request);

        $userId = $this->userRepository->store($user);
        try {
            $imagePath = self::saveImage($request, $userId);
        } catch (\Exception $e) {
            return new Response('Error:' . $e->getMessage());
        }
        if ($imagePath != null) {
            $user->setAvatarPath($imagePath);
            $this->userRepository->store($user);
        }

        return $this->redirectToRoute('show_user', ['userId' => $userId]);
    }

    public function updateUser(Request $request): Response
    {

        $userId = (int)$request->get('user_id');
        $user = $this->makeUserFromRequest($request);


        try {
            $imagePath = self::saveImage($request, $userId);
        } catch (\Exception $e) {
            return new Response('Error:' . $e->getMessage());
        }
        $user->setId($userId);
        $user->setAvatarPath($imagePath);
        $this->userRepository->store($user);


        return $this->redirectToRoute('show_user', ['userId' => $userId]);

    }


    public function getAllUsers(): Response
    {


        return $this->render('user/users_list.html.twig', [
            'users' => 'sd'
        ]);
    }

    private function makeUserFromRequest(Request $request): User
    {
        $birthDate = Utils::parseDateTime($request->get('birth_date'), self::DATE_TIME_FORMAT);
        $birthDate->setTime(0, 0, 0);

        return new User(
            null,
            empty($request->get('first_name')) ? null : $request->get('first_name'),
            empty($request->get('last_name')) ? null : $request->get('last_name'),
            empty($request->get('middle_name')) ? null : $request->get('middle_name'),
            empty($request->get('gender')) ? null : $request->get('gender'),
            $birthDate,
            empty($request->get('email')) ? null : $request->get('email'),
            empty($request->get('phone')) ? null : $request->get('phone'),
            empty($request->get('avatar_path')) ? null : $request->get('avatar_path'),
        );
    }

    public function showUser(int $userId): Response
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        return $this->render('/user/show_user.html.twig', [
            'id' => (string)$userId,
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'middle_name' => $user->getMiddleName(),
            'gender' => $user->getGender(),
            'birth_date' => Utils::convertDataTimeToString($user->getBirthDate()),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'avatar_path' => $user->getAvatarPath(),
        ]);
    }

    public function deleteUser(Request $request): Response
    {
        $userId = (int)$request->get('user_id');
        $user = $this->userRepository->findById($userId);
        $this->userRepository->delete($user);

        self::deleteUserAvatar($userId);

        return new Response('User deleted successfully');
    }


    private static function deleteUserAvatar(int $id): void
    {
        $uploadPath = Config::getUploadsPath();
        $file = $uploadPath . 'user_avatar' . $id;

        if (is_file($file . '.jpeg')) {
            unlink($file . '.jpeg');
        }

        if (is_file($file . '.png')) {
            unlink($file . '.png');
        }

        if (is_file($file . '.gif')) {
            unlink($file . '.gif');
        }

        if (is_file($file . '.jpg')) {
            unlink($file . '.jpg');
        }
    }

    private static function saveImage(Request $request, int $userId): null|Response|string
    {
        $uploadedFile = $request->files->get('avatar');


        if ($uploadedFile) {
            $uploadPath = Config::getUploadsPath();

            $mimeType = $uploadedFile->getClientMimeType();

            if ((!in_array($mimeType, self::VALID_IMAGE_TYPES))) {
                throw new \Exception('Unsupported file format');
            }

            $newFileName = 'user_avatar' . $userId . '.' . $uploadedFile->guessExtension();
            $uploadedFile->move($uploadPath, $newFileName);


            return '/Uploads/' . $newFileName;

        }
        return null;

    }

}