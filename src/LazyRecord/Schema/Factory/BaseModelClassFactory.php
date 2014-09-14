<?php
namespace LazyRecord\Schema\Factory;
use ClassTemplate\ClassTemplate;

class BaseModelClassFactory
{
    public static function create($schema, $baseClass) {
        $cTemplate = new ClassTemplate( $schema->getBaseModelClass() , array(
            'template' => 'Class.php.twig',
        ));
        $cTemplate->addConsts(array(
            'schema_proxy_class' => $schema->getSchemaProxyClass(),
            'collection_class' => $schema->getCollectionClass(),
            'model_class' => $schema->getModelClass(),
            'table' => $schema->getTable(),
        ));
        $cTemplate->addStaticVar( 'column_names',  $schema->getColumnNames() );
        $cTemplate->addStaticVar( 'column_hash',  array_fill_keys($schema->getColumnNames(), 1 ) );
        $cTemplate->addStaticVar( 'mixin_classes', array_reverse($schema->getMixinSchemaClasses()) );
        $cTemplate->extendClass( '\\' . $baseClass );
        return $cTemplate;
    }
}
