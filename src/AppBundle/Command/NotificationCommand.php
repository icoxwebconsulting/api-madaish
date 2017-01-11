<?php
namespace AppBundle\Command;

use AppBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NotificationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('notification:schedule')
            ->setDescription('Notification schedule')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('guzzle.client.api');

        $query = ['_format' => 'json'];
        $request = $client->get('Notification/GetNotifications', [
            'query' => $query
        ]);


        $request->getBody()->rewind();
        $response = json_decode($request->getBody()->getContents(), true);

        $cont = 0;
        if(sizeof($response) > 0)
        {
            $doctrine  = $this->getContainer()->get('doctrine');
            $em = $doctrine->getManager();
            foreach($response as $item)
            {
                $id = $item['NotificationId'];
                $title = $item['Title'];
                $text = $item['Text'];
                $type = $item['NotificationType'];
                $postType = $item['Type'];

                if(isset($item['FriendlyUrlUserName']))
                    $username = $item['FriendlyUrlUserName'];
                else
                    $username = null;

                if(isset($item['ContentFriendlyUrlname']))
                    $postName = $item['ContentFriendlyUrlname'];
                else
                    $postName = null;

                if($item['Devices'])
                {
                    foreach($item['Devices'] as $device)
                    {
                        $notification = new Notification();
                        $notification->setNotificationId($id);
                        $notification->setTitle($title);
                        $notification->setText($text);
                        $notification->setToken($device['Token']);
                        $notification->setOs($device['Os']);
                        $notification->setTypeId($type);

                        if(isset($item['ContentFriendlyUrlname']))
                        {
                            $notification->setPostType($postType);
                            $notification->setPostName($postName);
                        }

                        if(isset($item['FriendlyUrlUserName']))
                            $notification->setUserName($username);

                        $em->persist($notification);
                        $cont++;
                    }
                }
            }
            $em->flush();
        }

        $rsp = 'Has been saved '.$cont.' notifications';

        $output->writeln($rsp);
    }
}