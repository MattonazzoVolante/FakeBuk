
    <header>
        <nav>
            <i><a href="modsArea.php"><img src="../assets/icons/logoMods.png"  class="logo" alt=""></a></i>
            <i class="searchBar">
                <form action="searchReport.php" method="POST">
                    <i><input type="search" name="searchText" id="sText" placeholder="Cerca..."></i>
                    <i> <label for="inpSearch" id="sSend"> <img src="assets/icons/search.png" alt=""> </label> <input style="display:none;" id="inpSearch" type="submit" name="search" value=""></i>
                    <i> <input type="hidden" name="xaxaxaxaxaxaxaxa"></i>
                    <i>
                        <select type="search" name="filter" id="sFilter">
                            <option value="title">per titolo</option>
                            <option value="tags">per tag</option>
                            <option value="users">utente</option>
                        </select>
                    </i>
                </form>
            </i>
            <i><a href="../notificationCenter.php"><img id="notificationBell" src="../assets/icons/bell.png" alt=""></a></i>
            <i class="userSec">
                <?php global $logged; if(!$logged){ ?>
                    <i><a href="login.php">Registrati/accedi!</a></i> <i></i>
                <?php }else{ ?>
                    <i><a href='../userArea.php'><img id="immagineUtente" src="<?php echo "../".$_SESSION["pic_path"]; ?>" alt=""></a></i>
                    <i><p><?php echo $_SESSION["username"]; ?></p></i>
                <?php } ?>
            </i>
        </nav>
    </header>