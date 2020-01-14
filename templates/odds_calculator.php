<div class="odd_calculator col-12">
    <div class="odd_calculator_heading">
        Enter the Stake and Odds for your bet and the Bet Calculator will automatically
        calculate the Payout. And Odds for Multiples.
    </div>
    <hr>
    <form id="odds_form">
        Odds Format: 
        <select class="form-control" id="odds_format">
            <option value="american">American</option>
            <option value="fractal">Fractal</option>
            <option value="decimal">Decimal</option>
        </select>
        
        <div class="odds_error"></div>
        
        <div class="odd_container">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="Stake" class="odds_label text-center">Stake</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="Stake" class="odds_label text-center">Odds</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <input type="text" name="stake[]" class="form-control text-center odds_stake" placeholder="Enter stake">
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <input type="text" name="odds[]" class="form-control text-center odds_odds" placeholder="Odds">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <button type="button"  style="width:100%" class="odd_add">Add</button>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <button type="button" style="width:100%" id="odd_calc">Calc</button>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="row">
                <div class="col-6 text-right lh50">
                    Payout
                </div>
                <div class="col-6">
                    <div class="form-group odds_payout">

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>