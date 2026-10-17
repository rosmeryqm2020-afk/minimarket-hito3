package com.minimarket;

import jakarta.inject.Inject;
import jakarta.ws.rs.*;
import jakarta.ws.rs.core.*;
import javax.sql.DataSource;
import java.sql.*;
import java.util.*;

@Path("/productos")
@Produces(MediaType.APPLICATION_JSON)
@Consumes(MediaType.APPLICATION_JSON)
public class ProductoResource {

    @Inject
    DataSource ds;

    @GET
    public Response listar() throws SQLException {
        List<Producto> productos = new ArrayList<>();
        String sql = "SELECT * FROM Producto WHERE eliminado=0";

        try (Connection c = ds.getConnection();
             PreparedStatement ps = c.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            while (rs.next()) {
                productos.add(leer(rs));
            }
        }
        return Response.ok(productos).build();
    }

    @POST
    public Response crear(Producto p) throws SQLException {
        if (!valido(p)) return Response.status(400).build();

        String sql = "INSERT INTO Producto " +
            "(nombre, proveedor, fecha_vencimiento, precio, stock) " +
            "VALUES (?,?,?,?,?)";

        try (Connection c = ds.getConnection();
             PreparedStatement ps = c.prepareStatement(sql)) {
            ps.setString(1, p.nombre);
            ps.setString(2, p.proveedor);
            ps.setDate(3, java.sql.Date.valueOf(p.fecha_vencimiento));
            ps.setBigDecimal(4, p.precio);
            ps.setInt(5, p.stock);
            ps.executeUpdate();
        }
        return Response.status(201).build();
    }

    @PUT
    @Path("/{id}")
    public Response editar(@PathParam("id") int id, Producto p)
            throws SQLException {
        if (!valido(p)) return Response.status(400).build();

        String sql = "UPDATE Producto SET nombre=?, proveedor=?, " +
            "fecha_vencimiento=?, precio=?, stock=? " +
            "WHERE id=? AND eliminado=0";

        try (Connection c = ds.getConnection();
             PreparedStatement ps = c.prepareStatement(sql)) {
            ps.setString(1, p.nombre);
            ps.setString(2, p.proveedor);
            ps.setDate(3, java.sql.Date.valueOf(p.fecha_vencimiento));
            ps.setBigDecimal(4, p.precio);
            ps.setInt(5, p.stock);
            ps.setInt(6, id);
            return ps.executeUpdate() > 0
                ? Response.ok().build()
                : Response.status(404).build();
        }
    }

    @DELETE
    @Path("/{id}")
    public Response eliminar(@PathParam("id") int id)
            throws SQLException {
        try (Connection c = ds.getConnection();
             PreparedStatement ps = c.prepareStatement(
                "UPDATE Producto SET eliminado=1 WHERE id=? AND eliminado=0")) {
            ps.setInt(1, id);
            return ps.executeUpdate() > 0
                ? Response.noContent().build()
                : Response.status(404).build();
        }
    }

    private boolean valido(Producto p) {
        return p != null
            && p.nombre != null && !p.nombre.isBlank()
            && p.nombre.length() <= 100
            && p.proveedor != null && !p.proveedor.isBlank()
            && p.proveedor.length() <= 100
            && p.fecha_vencimiento != null
            && p.precio != null && p.precio.signum() >= 0
            && p.stock != null && p.stock >= 0;
    }

    private Producto leer(ResultSet rs) throws SQLException {
        Producto p = new Producto();
        p.id = rs.getInt("id");
        p.nombre = rs.getString("nombre");
        p.proveedor = rs.getString("proveedor");
        p.fecha_vencimiento =
            rs.getDate("fecha_vencimiento").toLocalDate();
        p.precio = rs.getBigDecimal("precio");
        p.stock = rs.getInt("stock");
        p.eliminado = rs.getBoolean("eliminado");
        return p;
    }
}