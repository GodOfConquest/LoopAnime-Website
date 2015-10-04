<?php

namespace LoopAnime\UsersBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use LoopAnime\UsersBundle\Entity\Countries;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserCPFormType extends AbstractType
{

    public function __construct(ObjectManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $em = $this->em->getRepository('LoopAnimeUsersBundle:Countries');
        /** @var Countries[] $countries */
        $countries = $em->findAll();

        $country_arr = ['GB' => 'United Kingdom', 'ES' => 'Spain', 'FR' => 'France', 'US' => 'Unisted States of America', 'PT' => 'Portugal', 'BR' => 'Brazil'];
        foreach ($countries as $country) {
            $country_arr[$country->getIso2()] = $country->getDescription();
        }

        $commonLabelAttr = ['class' => 'font-bold'];

        $builder
            ->add('username', null,
                array(
                    'label' => 'Username:',
                    'attr' => ['class' => 'form-control input-small', 'placeholder' => 'Username'],
                    'label_attr' => $commonLabelAttr,
                ))
            ->add('birthdate', 'date', array(
                'widget' => 'single_text',
                'label' => 'Birthday:',
                'label_attr' => $commonLabelAttr,
                'attr' => ['class' => 'form-control input-small', 'placeholder' => 'Birthday'],
                'empty_value' => array('year' => 'Year', 'month' => 'Month', 'day' => 'Day')
            ))
            ->add('newsletter', 'checkbox', array(
                'required' => false,
                'label' => 'Newsletter:',
                'attr' => [],
                'label_attr' => $commonLabelAttr,
            ))
            ->add('buttonSubmit', 'submit', array(
                'label' => 'Submit Changes',
                'attr' => ['class' => 'btn btn-small btn-success']
            ))
            ->add('avatarFile', 'file', array(
                'label' => 'Avatar File:',
                'label_attr' => $commonLabelAttr,
                'attr' => ['class' => 'form-control input-small', 'placeholder' => 'Avatar'],
                'mapped' => false,
                'required' => false
            ))
            ->add('lang', 'choice', array(
                'label' => 'Language:',
                'label_attr' => $commonLabelAttr,
                'attr' => ['class' => 'form-control input-small', 'placeholder' => 'Language'],
                "choices" => ['PT' => 'Portuguese-Brazilian', 'EN' => 'English', 'ES' => 'Spanish', 'FR' => 'French']
            ))
            ->add('country', 'choice', array(
                'label' => 'Country:',
                'label_attr' => $commonLabelAttr,
                'attr' => ['class' => 'form-control input-small', 'placeholder' => 'Country'],
                "choices" => $country_arr
            ))
            ->add('email', 'email', array(
                "label" => 'Email:',
                'attr' => ['class' => 'form-control input-small', 'placeholder' => 'E-Mail'],
                'label_attr' => $commonLabelAttr,
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'LoopAnime\UsersBundle\Entity\Users',
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'loopanime_user_usercp';
    }

}
