## Integración Woocommerce - Intcomex - ICECAT

## Funcionalidades del plugin:

### Intcomex

- Sincronización del catálogo básico de productos de Intcomex.
- Sincronización del catálogo extendido de productos de Intcomex.
- Sincronización de la lista de precios.
- Sincronización del inventario.
- Sincronización de pedidos 

### IceCat
- Sincronización de las imágenes y nombres de productos.

## Documentación técnica

### CRON

Existen tres tareas cron que se pueden configurar desde la administración del plugin.
Los identificadores de cada tarea son:

- **bwi_sync_products_data**: Sincroniza información de productos (Intcomex y IceCat)
- **bwi_sync_products_inventory**: Sincroniza la información de inventario
- **bwi_sync_products_prices**: Sincroniza los precios de productos utilizando el valor USD observado (API SBIF)

El log de cada una de estas tareas se puede revisar en la siguiente ruta:

- wp-content/uploads/bwi-logs/bwi_sync_products_data.log
- wp-content/uploads/bwi-logs/bwi_sync_products_inventory.log
- wp-content/uploads/bwi-logs/bwi_sync_products_prices.log


Para ejecutar un cron especifico desde una consola con WP-CLI:

- wp cron event run bwi_sync_products_data
