<?php
namespace AppBundle\Command;

use AppBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SendIonicCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('notification:send:ionic')
            ->setDescription('Notification ionic')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine  = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $cont = 0;

        // notifications android
        $notifications = $em->getRepository('AppBundle:Notification')->findBy(array('send' => null));

        if(sizeof($notifications) > 0)
        {
            foreach($notifications as $notification)
            {
                $extra = array();

                switch ($notification->getTypeId())
                {
                    case 0:
                        $extra['username'] = $notification->getUserName();
                        $extra['post_type'] = $notification->getPostType();
                        $extra['post_name'] = $notification->getPostName();
                        $extra['username'] = $notification->getUserName();
                        $extra['type'] = 0;
                        break;
                    case 1:
                        $extra['type'] = 1;
                        $extra['username'] = $notification->getUserName();
                        break;
                    case 2:
                        $extra['type'] = 2;
                        break;
                }


                $stats = $this->getContainer()->get('push_notification.ionic')->sendNotification(
                    [$notification->getToken()],
                    $notification->getTitle(),
                    $notification->getText(),
                    $extra,
                    null);

                if(isset($stats['status']))
                {
//                    $notification->setSend(true);
//                    $em->persist($notification);
                    $cont++;

                }
            }

            $em->flush();
        }


        $rsp = 'Has been sent '.$cont.' notifications';
        $output->writeln($rsp);
    }
}