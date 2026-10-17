package com.minimarket;

import java.math.BigDecimal;
import java.time.LocalDate;

public class Producto {
    public Integer id;
    public String nombre;
    public String proveedor;
    public LocalDate fecha_vencimiento;
    public BigDecimal precio;
    public Integer stock;
    public Boolean eliminado;
}