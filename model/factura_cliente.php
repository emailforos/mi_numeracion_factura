<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2013-2016  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'plugins/facturacion_base/model/core/factura_cliente.php';

/**
 * Factura de un cliente.
 * 
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class factura_cliente extends FacturaScripts\model\factura_cliente
{
    public function new_codigo() {
             /* Versión nueva */
      /// buscamos un hueco
      
      $num = 1;
      if( defined('FS_NFACTURA_CLI') )
      {
         /// mantenemos compatibilidad con versiones anteriores
         $num = intval(FS_NFACTURA_CLI);
      }
      $serie0 = new serie();
      $serie = $serie0->get($this->codserie);
      if($serie)
      {
         /// ¿Se ha definido un nº de factura inicial para esta serie y ejercicio?
         if($this->codejercicio == $serie->codejercicio)
         {
            $num = $serie->numfactura;
         }
      }
      
      //buscamos un hueco //
      $encontrado = FALSE;
      $fecha = $this->fecha;
      $data = $this->db->select("SELECT ".$this->db->sql_to_int('numero')." as numero,fecha
         FROM ".$this->table_name." WHERE codejercicio = ".$this->var2str($this->codejercicio).
         " AND codserie = ".$this->var2str($this->codserie)." ORDER BY numero ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            if( intval($d['numero']) < $num )
            {
               /**
                * El número de la factura es menor que el inicial.
                * El usuario ha cambiado el número inicial después de hacer
                * facturas.
                */
            }
            else if( intval($d['numero']) == $num )
            {
               /// el número es correcto, avanzamos
               $num++;
            }
            else
            {
               /// Hemos encontrado un hueco y debemos usar el número y la fecha.
               $encontrado = TRUE;
               $fecha = Date('d-m-Y', strtotime($d['fecha']));
               break;
            }
         }
      }
      if($encontrado)
      {
         $this->numero = $num;
         $this->fecha = $fecha;
      }
      else
      {
         $this->numero = $num;
         
         /// nos guardamos la secuencia para abanq/eneboo
         $sec = new secuencia();
         $sec = $sec->get_by_params2($this->codejercicio, $this->codserie, 'nfacturacli');
         if($sec)
         {
            if($sec->valorout <= $this->numero)
            {
               $sec->valorout = 1 + $this->numero;
               $sec->save();
            }
         }
      }
      
      if(FS_NEW_CODIGO == 'eneboo')
      {
         if ($this->codserie == "A") {
            $this->codigo = $this->codejercicio.'-'.sprintf('%04s', $this->numero);
         } else if ($this->codserie == "XI") {
            $this->codigo = $this->codserie.$this->codejercicio.'-'.sprintf('%04s', $this->numero);
         }  else if ($this->codserie == "XF") {
            $this->codigo = $this->codserie.$this->codejercicio.'-'.sprintf('%04s', $this->numero);
         }  else {
            $this->codigo = $this->codejercicio.sprintf('%02s', $this->codserie).sprintf('%06s', $this->numero);
         }
      }
      else
      {
          if ($this->codserie=="A"){
              $this->codigo = 'FAC'.$this->numero;
          }
          else
          {    
            $this->codigo = 'FAC'.$this->codejercicio.$this->codserie.$this->numero;
          }
      }
    }
}
