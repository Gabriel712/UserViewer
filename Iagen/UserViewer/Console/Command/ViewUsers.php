<?php
declare(strict_types=1);

namespace Iagen\UserViewer\Console\Command;

use Magento\Customer\Model\Visitor;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Customer\Model\Customer;


class ViewUsers extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("iagen_userviewer:viewusers");
        $this->setDescription("View users online");
        $this->addArgument(
            'datetime',
            InputArgument::OPTIONAL,
            'Specify a date and time in the format Y-m-d H:i:s'
        );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */


     protected function execute(InputInterface $input, OutputInterface $output)
     {
         $objectManager = ObjectManager::getInstance();
         $visitorCollection = $objectManager->create('Magento\Customer\Model\ResourceModel\Visitor\Collection');
         $visitorCollection->getSelect()->columns([
             'visitor_id',
             'customer_id',
             'session_id',
             'last_visit_at',
             'created_at',
         ]);
     
         // Adiciona a condição de filtro pela hora atual
         $datetime = date($input->getArgument('datetime'));
         if (!$datetime) {
             $datetime = date('Y-m-d H');
         }
         $visitorCollection->addFieldToFilter('last_visit_at', ['gteq' => new \Zend_Db_Expr("'$datetime'")]);
     
         $visitorData = $visitorCollection->getData();
         $output->writeln('System time: '. $datetime);

         $output->writeln('Visitors:');
         foreach ($visitorData as $visitor) {
             $output->writeln(' - ID: ' . $visitor['visitor_id']);
             $output->writeln('   Customer ID: ' . $visitor['customer_id']);

             $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($visitor['customer_id']);
             $customerName = $customer->getName();
             $output->writeln('   Customer Name: ' . $customerName);

             //$output->writeln('   Session ID: ' . $visitor['session_id']);
             $output->writeln('   Last Visit: ' . $visitor['last_visit_at']);
             $output->writeln('   Created At: ' . $visitor['created_at']);
         }

         $visitorCount = $visitorCollection->getSize();
         $output->writeln('Total visitors : '. $visitorCount);

     }

}
