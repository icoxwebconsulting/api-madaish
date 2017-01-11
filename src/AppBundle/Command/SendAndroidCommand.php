<?php
namespace AppBundle\Command;

use AppBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendAndroidCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('notification:send:android')
            ->setDescription('Notification android')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine  = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $cont = 0;

        // notifications android
        $notifications = $em->getRepository('AppBundle:Notification')->findBy(array('send' => null, 'os' => 0));

        if(sizeof($notifications) > 0)
        {
            foreach($notifications as $notification)
            {
                $extra = array();
//                $extra = ['type' => 2];
//                $extra['sender'] = $message->getCustomer()->getUsername();
                
                $stats = $this->getContainer()->get('push_notification.fcm')->sendNotification(
                    [$notification->getToken()],
                    $notification->getTitle(),
                    $notification->getText(),
                    $extra,
                    null);

               if(isset($stats['successful']))
               {
                   $notification->setSend(true);
                   $em->persist($notification);
                   $cont++;

               }
            }

            $em->flush();
        }


        $rsp = 'Has been sent '.$cont.' notifications';
        $output->writeln($rsp);
    }
}