<?php

namespace App\DataFixtures;

use App\Entity\Ad;
use Faker\Factory;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Booking;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    //Nous mettons en place le hash du mot de passe (en plus de ce qui a été ajouté dans le security.yaml)
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');
        
        //On crée un nouveau rôle administrateur
        $adminRole = new Role();
        $adminRole->setTitle('ROLE_ADMIN');
        $manager->persist($adminRole);

        //On crée un utilisateur qui aura le rôle admin
        $adminUser = new User();
        $adminUser->setFirstname('Jean-Charles')
                    ->setLastName('Graillot')
                    ->setEmail('grooveman59@yahoo.fr')
                    ->setHash($this->encoder->encodePassword($adminUser, 'password'))
                    ->setPicture('https://avatars.io/gravatar/GJC')
                    ->setIntroduction($faker->sentence())
                    ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(3)) . '</p>')
                    ->addUserRole($adminRole);
        
        $manager->persist($adminUser);
                    
        
        //Nous gérons les utilisateurs
        $users = [];
        $genres = ['male', 'female'];

        for ($i=1; $i <= 10; $i++) { 

           $user= new User();

           $genre = $faker->randomElement($genres); //randomElement est une méthode de Faker pour choisir aléatoirement une entrée dans un tableau
        
            $picture = 'https://randomuser.me/api/portraits/';
            $pictureId = $faker->numberBetween(1, 99) . '.jpg';

            $picture = $picture . ($genre == 'male' ? 'men/' : 'women/') . $pictureId; //Condition ternaire ($picture.= possible)
            
            $hash = $this->encoder->encodePassword($user, 'password');

           $user->setFirstname($faker->firstname($genre))
                ->setLastName($faker->lastname)
                ->setEmail($faker->email)
                ->setIntroduction($faker->sentence())
                ->setDescription('<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>')
                ->setHash($hash)
                ->setPicture($picture);

            $manager->persist($user);
            $users[] = $user;
        }
        
        //Nous gérons les annonces
        for ($i=1; $i <=30 ; $i++) { 
           
        $ad = new Ad;

        $title = $faker->sentence();
        $coverImage = $faker->imageUrl(1000, 350);
        $introduction = $faker->paragraph(2);
        $content = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>';
        $user = $users[mt_rand(0, count($users) - 1)];

        $ad->setTitle($title)
            ->setcoverImage($coverImage)
            ->setIntroduction($introduction)
            ->setContent($content)
            ->setPrice(mt_rand(40, 200))
            ->setRooms(mt_rand(1, 5))
            ->setAuthor($user);
        
        //Nous gérons les images
        for ($j = 1; $j <= mt_rand(2, 5); $j++) { 
            $image = new Image();
            $image->setUrl($faker->imageUrl())
                ->setCaption($faker->sentence())
                ->setAd($ad);

            $manager->persist($image);
        }

        //Gestion des réservations
        for ($j = 1; $j <= mt_rand(0, 10) ; $j++) {

            $booking = new Booking();

            $createdAt = $faker->dateTimeBetween('-6 months');

            $startDate = $faker->dateTimeBetween('-3 months');

            $duration = mt_rand(3, 10);
            //On clone $startDate pour éviter qu'il soit égal à $endDate
            $endDate = (clone $startDate)->modify("+$duration days");

            $amount = $ad->getPrice()*$duration;
            
            $booker = $users[mt_rand(0, count($users) - 1)];

            $comment = $faker->paragraph();

            $booking->setBooker($booker)
                    ->setAd($ad)
                    ->setStartDate($startDate)
                    ->setEndDate($endDate)
                    ->setCreatedAt($createdAt)
                    ->setAmount($amount)
                    ->setComment($comment);
            
            $manager->persist($booking);

            //Gestion des commentaires
            if (mt_rand(0, 1)) {
                $comment = new Comment();
                $comment->setContent($faker->paragraph())
                        ->setRating(mt_rand(1, 5))
                        ->setAuthor($booker)
                        ->setAd($ad);

                $manager->persist($comment);
            }
        }

        $manager->persist($ad);

        }

        $manager->flush();

    }
}
