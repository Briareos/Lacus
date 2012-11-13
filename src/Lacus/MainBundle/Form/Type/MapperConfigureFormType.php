<?php

namespace Lacus\MainBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Lacus\MainBundle\Entity\Mapper;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class MapperConfigureFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'mapper_configure';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) use ($factory) {
                $mapper = $event->getData();
                if (!$mapper instanceof Mapper) {
                    return;
                }
                if (!$mapper->getLoginRequired()) {
                    return;
                }
                $defaultAccountField = $factory->createNamed(
                    'defaultAccount',
                    'entity',
                    null,
                    array(
                        'class' => 'Lacus\MainBundle\Entity\Account',
                        'property' => 'username',
                        'query_builder' => function (EntityRepository $er) use ($mapper) {
                            return $er->createQueryBuilder('a')
                              ->where('a.site = :site')
                              ->setParameter('site', $mapper->getSite())
                              ->orderBy('a.id', 'ASC');
                        },
                    )
                );
                $event->getForm()->add($defaultAccountField);
            }
        );

        $builder->add(
            'id',
            'hidden',
            array(
                'disabled' => true,
            )
        );
        $builder->add(
            'data',
            'mapper_data',
            array(
                'required' => true,
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Lacus\MainBundle\Entity\Mapper',
            )
        );
    }
}