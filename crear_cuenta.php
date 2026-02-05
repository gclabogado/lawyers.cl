<h2>Crear Cuenta</h2>
<form action="registro.php" method="post">
    <label for="username">Correo Electrónico:</label>
    <input type="email" id="username" name="username" required>
    <label for="password">Contraseña:</label>
    <input type="password" id="password" name="password" required>
    <label for="categoria">Categoría:</label>
    <select id="categoria" name="categoria" required>
        <option value="gratuita">Gratuita</option>
        <option value="donador">Donador</option>
    </select>
    <input type="submit" value="Registrar">
</form>
