#!/bin/bash


ls -lhsa

CONDICION1=cat errores_composer.log | grep 'error\|critical'
echo ""

FICHERO=errores_composer.log
if [ -f $FICHERO ]
then
   echo "El fichero $FICHERO y $CONDICION1 existe"
else
   echo "El fichero $FICHERO o $CONDICION1 no existe"
fi


FICHERO2=../errores_composer_fail.log
if [ -f $FICHERO2 ]
then
   echo "El fichero $FICHERO2 y $CONDICION1 existe"
else
   echo "El fichero $FICHERO2 o $CONDICION1 no existe"
fi

exit 1

