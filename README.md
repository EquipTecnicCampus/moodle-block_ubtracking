Block ubtracking


Instrucciones de instalación:
Descomprimir el fichero en el directorio blocks de la instalación de moodle e ir a notificaciones en el campus


Instrucciones de uso:

  Inicialmente se debe definir un cuestionario como prueba de nivel para la creación de grupos.

  Una vez tenemos usuarios que respondan dicho cuestionario, creamos grupos o distribuciones (estas se pueden configurar en el propio bloque):
    Distribuciones equilibradas:
      Los usuarios son de un mismo nivel en la prueba de nivel.
    Distribuciones compensadas:
      Los usuarios son de diferentes niveles en la prueba de nivel.    
    
  Con las distribuciones hechas pasamos a añadir actividades a estos grupos de usuarios, creando a la vez grupos de actividades.
    los resultados de estas agrupaciones de actividades para con los grupos se puede ver en las estadísticas del bloque.

Cálculo de estadísticas:
Definimos el proceso de cálculo de las estadísticas para grupos y usuarios:

·Por grupos
  por cada uno de los grupos de usuarios se evalúan sus actividades separando por agrupación de actividades
  y revisando para cada actividad, el resultado de cada usuario.

  Por cada grupo de actividades:
      Miramos cada actividad:
          Evaluamos por cada usuario:
              Activamos FLAGS según:
              si está superado
              o
              pendiente de evaluar por el profesor
          Al acabar de revisar todos los usuarios,
      se evalúa la actividad
        esa actividad debe estar superada o pendiente para continuar evaluando,
        en caso contrario marcamos el semáforo dl grupo en ROJO y dejamos el bucle
  Acabadas de mirar todas las actividades.
  si está activo el FLAG de pendiente se marca el semáforo NARANJA
  sino, y está el de superado, se marca el semáforo en VERDE

·Por usuario
  por cada uno de los usuarios se evalúan sus actividades separando por agrupación de actividades
  y revisando para cada actividad, el resultado de cada usuario.

  Por cada grupo de actividades:
      Miramos cada actividad:
          Activamos FLAGS según:
          si está superado
          o
          pendiente de evaluar por el profesor
      si no se activa ningún FLAG marcamos el semáforo del usuario en ROJO y dejamos el bucle
  Acabadas de mirar todas las actividades.
  si está activo el FLAG de pendiente se marca el semáforo NARANJA
  sino, y está el de superado, se marca el semáforo en VERDE

