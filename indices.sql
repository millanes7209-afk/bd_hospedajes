
-- para busquedas por cedula --------------------------------
CREATE INDEX idx_ci ON clientes(ci);

-- para busquedas por nombres --------------------------------
CREATE INDEX idx_nombres ON clientes(nombres);

-- para busquedas por apellidos --------------------------------
CREATE INDEX idx_apellidos ON clientes(apellidos);
